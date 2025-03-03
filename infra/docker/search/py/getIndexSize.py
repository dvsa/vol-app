import json
import sys

# get json from stdin
data = json.load(sys.stdin)

try:
    index = list(data['indices'].keys())[0]
    print((data['indices'][index]['primaries']['docs']['count']))
except Exception as e:
    print(0)
