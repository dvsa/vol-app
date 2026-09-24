import fs from "node:fs";
import path from "node:path";
import createDebug from "debug";
import exec from "../exec";

const debug = createDebug("refresh:utils:ProdDataMerge");

export enum MergeWinner {
  PROD,
  LOCAL,
}

interface ColumnMeta {
  name: string;
  primary: boolean;
  autoIncrement: boolean;
  generated: boolean;
}

export interface TableMeta {
  name: string;
  prodColumns: ColumnMeta[];
  localColumns: ColumnMeta[];
}

interface MatchRule {
  // Columns that identify the same row in both databases
  on: string[];
  // A column holding a parent's id, matched through the parent's own key because the ids differ
  parent?: { column: string; table: string; id: string; key: string };
}

// These tables get auto-assigned ids from ETL patches, so the same row usually has a different id
// locally and in prod. Everything else is matched on its primary key.
const MATCH_RULES: Record<string, MatchRule> = {
  translation_key: { on: ["translation_key"] },
  translation_key_text: {
    on: ["translation_key_id", "language_id"],
    parent: { column: "translation_key_id", table: "translation_key", id: "id", key: "translation_key" },
  },
  feature_toggle: { on: ["config_name"] },
  replacement: { on: ["placeholder"] },
  template: { on: ["locale", "format", "name"] },
};

// Parents go before the tables that point at them
const MERGE_FIRST = ["translation_key", "translation_key_text", "template_test_data"];

export const SUMMARY_MARKER = "MERGE_SUMMARY";

export interface TableSummary {
  table: string;
  inBoth: number;
  prodOnly: number;
  localOnly: number;
}

const q = (identifier: string): string => "`" + identifier.replace(/`/g, "``") + "`";

const orderTables = (tables: TableMeta[]): TableMeta[] => {
  const rank = (name: string) => {
    const index = MERGE_FIRST.indexOf(name);
    return index === -1 ? MERGE_FIRST.length : index;
  };

  return [...tables].sort((a, b) => rank(a.name) - rank(b.name) || a.name.localeCompare(b.name));
};

/**
 * Builds SQL that merges prod rows from prodSchema into localSchema, table by table, leaving the local
 * table definitions and triggers alone.
 *
 * Rows only in prod are added and rows only in local are kept. Rows in both take the winner's values.
 */
export const buildMergeSql = (
  tables: TableMeta[],
  prodSchema: string,
  localSchema: string,
  winner: MergeWinner,
): { sql: string; skipped: string[] } => {
  const statements: string[] = [
    "SET SESSION sql_mode = 'NO_AUTO_VALUE_ON_ZERO,NO_ENGINE_SUBSTITUTION';",
    // The history triggers skip themselves when this is set, so the merge doesn't fill the _hist tables
    "SET @DISABLE_TRIGGERS = 1;",
    "SET FOREIGN_KEY_CHECKS = 0;",
    "START TRANSACTION;",
  ];
  const skipped: string[] = [];

  for (const table of orderTables(tables)) {
    if (table.localColumns.length === 0) {
      skipped.push(`${table.name} (not in the local database)`);
      continue;
    }

    const localByName = new Map(table.localColumns.map((column) => [column.name, column]));
    const common = table.prodColumns
      .map((column) => column.name)
      .filter((name) => localByName.has(name) && !localByName.get(name)!.generated);
    const primary = table.localColumns.filter((column) => column.primary);
    const rule: MatchRule = MATCH_RULES[table.name] ?? { on: primary.map((column) => column.name) };

    if (rule.on.length === 0 || !rule.on.every((name) => common.includes(name))) {
      skipped.push(`${table.name} (no key to match rows on)`);
      continue;
    }

    const matchesOnPrimary =
      primary.length === rule.on.length && primary.every((column) => rule.on.includes(column.name));
    // A new row gets its own local id when rows aren't matched on the id
    const skipIdOnInsert = !matchesOnPrimary && primary.length === 1 && primary[0].autoIncrement;

    const parent = rule.parent;
    const source = parent
      ? `(${q(prodSchema)}.${q(table.name)} p ` +
        `JOIN ${q(prodSchema)}.${q(parent.table)} pp ON pp.${q(parent.id)} = p.${q(parent.column)} ` +
        `JOIN ${q(localSchema)}.${q(parent.table)} lp ON lp.${q(parent.key)} = pp.${q(parent.key)})`
      : `${q(prodSchema)}.${q(table.name)} p`;
    const value = (name: string) => (parent && name === parent.column ? `lp.${q(parent.id)}` : `p.${q(name)}`);
    const matches = (alias: string) => rule.on.map((name) => `${alias}.${q(name)} <=> ${value(name)}`).join(" AND ");
    const localTable = `${q(localSchema)}.${q(table.name)}`;
    const existsLocally = `EXISTS (SELECT 1 FROM ${localTable} l WHERE ${matches("l")})`;

    statements.push(
      `SELECT '${SUMMARY_MARKER}', '${table.name}', ` +
        `(SELECT COUNT(*) FROM ${source} WHERE ${existsLocally}), ` +
        `(SELECT COUNT(*) FROM ${source} WHERE NOT ${existsLocally}), ` +
        `(SELECT COUNT(*) FROM ${localTable} l WHERE NOT EXISTS (SELECT 1 FROM ${source} WHERE ${matches("l")}));`,
    );

    const updateColumns = common.filter(
      (name) => !rule.on.includes(name) && !primary.some((column) => column.name === name),
    );

    if (winner === MergeWinner.PROD && updateColumns.length > 0) {
      statements.push(
        `UPDATE ${localTable} l JOIN ${source} ON ${matches("l")} ` +
          `SET ${updateColumns.map((name) => `l.${q(name)} = ${value(name)}`).join(", ")};`,
      );
    }

    const insertColumns = common.filter((name) => !(skipIdOnInsert && name === primary[0].name));

    statements.push(
      `INSERT INTO ${localTable} (${insertColumns.map(q).join(", ")}) ` +
        `SELECT ${insertColumns.map(value).join(", ")} FROM ${source} WHERE NOT ${existsLocally};`,
    );
  }

  statements.push("COMMIT;");

  return { sql: statements.join("\n"), skipped };
};

export const parseMergeSummary = (output: string): TableSummary[] =>
  output
    .split("\n")
    .map((line) => line.split("\t"))
    .filter((fields) => fields[0] === SUMMARY_MARKER && fields.length === 5)
    .map(([, table, inBoth, prodOnly, localOnly]) => ({
      table,
      inBoth: Number(inBoth),
      prodOnly: Number(prodOnly),
      localOnly: Number(localOnly),
    }));

const mysql = (args: string): string => `docker compose exec -T db mysql -uroot -polcs ${args}`;

const readTableMeta = (prodSchema: string, localSchema: string): TableMeta[] => {
  const output = exec(
    mysql(
      `-N -B -e "SELECT TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, COLUMN_KEY, EXTRA FROM information_schema.COLUMNS ` +
        `WHERE TABLE_SCHEMA IN ('${prodSchema}', '${localSchema}') AND TABLE_NAME IN (SELECT TABLE_NAME FROM information_schema.TABLES ` +
        `WHERE TABLE_SCHEMA = '${prodSchema}' AND TABLE_TYPE = 'BASE TABLE') ORDER BY TABLE_SCHEMA, TABLE_NAME, ORDINAL_POSITION"`,
    ),
    debug,
  ).stdout;

  const tables = new Map<string, TableMeta>();

  for (const line of output.split("\n").filter(Boolean)) {
    const [schema, tableName, columnName, columnKey, extra = ""] = line.split("\t");
    const table = tables.get(tableName) ?? { name: tableName, prodColumns: [], localColumns: [] };
    const column: ColumnMeta = {
      name: columnName,
      primary: columnKey === "PRI",
      autoIncrement: extra.includes("auto_increment"),
      generated: /\b(VIRTUAL|STORED) GENERATED\b/.test(extra),
    };

    (schema === prodSchema ? table.prodColumns : table.localColumns).push(column);
    tables.set(tableName, table);
  }

  return [...tables.values()];
};

/**
 * Loads a prod dump into a scratch schema and merges its rows into the local database, instead of
 * piping it straight in. The dump drops and recreates its tables, which would throw away rows and
 * columns added by local ETL patches, and the tables' triggers with them.
 */
export const mergeProdDump = (
  etlDirectory: string,
  dumpFileName: string,
  localSchema: string,
  winner: MergeWinner,
): { summary: TableSummary[]; skipped: string[] } => {
  const prodSchema = `${localSchema}_prod_anon`;
  const sqlFileName = ".local-refresh-merge.sql";

  try {
    exec(mysql(`-e "DROP DATABASE IF EXISTS ${prodSchema}; CREATE DATABASE ${prodSchema}"`), debug);
    exec(
      `docker compose exec -T db /bin/bash -c 'zcat /var/lib/etl/${dumpFileName} | mysql -uroot -polcs ${prodSchema}'`,
      debug,
    );

    const { sql, skipped } = buildMergeSql(readTableMeta(prodSchema, localSchema), prodSchema, localSchema, winner);

    fs.writeFileSync(path.join(etlDirectory, sqlFileName), sql);

    const output = exec(
      `docker compose exec -T db /bin/bash -c 'mysql -uroot -polcs -N -B < /var/lib/etl/${sqlFileName}'`,
      debug,
    ).stdout;

    return { summary: parseMergeSummary(output), skipped };
  } finally {
    fs.rmSync(path.join(etlDirectory, sqlFileName), { force: true });
    exec(mysql(`-e "DROP DATABASE IF EXISTS ${prodSchema}"`), debug);
  }
};
