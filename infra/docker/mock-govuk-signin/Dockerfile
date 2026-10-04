FROM public.ecr.aws/lambda/nodejs:22

WORKDIR ${LAMBDA_TASK_ROOT}

# Copy package files and install dependencies
COPY app/mock-govuk-signin/package*.json ./
RUN npm ci

# Copy only the inputs needed to build (avoids dragging in host node_modules/
# or a stale dist/ from the workspace; the build context is the repo root,
# which has no .dockerignore, so listing inputs explicitly here is safer
# than a wildcard COPY).
COPY app/mock-govuk-signin/tsconfig.json ./
COPY app/mock-govuk-signin/src ./src

# Build TypeScript
RUN npm run build

# Copy PEM files to correct location
COPY app/mock-govuk-signin/mock-*.pem ./

# Copy and set up entrypoint script
COPY infra/docker/mock-govuk-signin/entrypoint.sh ./
RUN chmod +x entrypoint.sh

ENTRYPOINT ["./entrypoint.sh"]