import sys
import json

# get alias name we're interested in
aliases = sys.argv
# remove script name
aliases.pop(0)

#get json from stdin
data = json.load(sys.stdin)

indexWithoutAlias = []
for index in data:
    # find position of _v in index name
    pos = index.rfind('_v')
    # if _v was found
    if pos != -1:
        # if index (without _vXXXXX) is in aliases we want to check
        if index[:pos] in aliases:
            # if this index does not have an alias
            if len(data[index]['aliases']) == 0:
                indexWithoutAlias.append(index)

print(','.join(indexWithoutAlias))
