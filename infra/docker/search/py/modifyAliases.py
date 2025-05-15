import json
from pprint import pprint
import sys

# get alias name we're interested in
aliases = sys.argv
# remove script name
aliases.pop(0)
# new version
newVersion = aliases.pop(0)

#get json from stdin
data = json.load(sys.stdin)


actions = []

for alias in aliases:
    actions.append({"add" : {"index" : alias +'_v'+ newVersion, "alias" : alias}})

for index in data:
    for alias in data[index]['aliases']:
        if alias in aliases:
            actions.append( { "remove" : { "index" : index , "alias" : alias } } )

print(json.dumps({'actions' : actions}))
