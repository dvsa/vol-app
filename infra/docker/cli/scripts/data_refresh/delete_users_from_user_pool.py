#!/opt/venv/bin/python

import sys
import boto3

userPoolId = sys.argv[1]
region = sys.argv[2]


def get_user_pool_users(userPoolId, region):
    users = []
    justUserNames = []

    client = boto3.client('cognito-idp', region_name=region)

    next_page = None
    kwargs = {
        'UserPoolId': userPoolId
    }

    users_remain = True
    while users_remain:
        if next_page:
            kwargs['PaginationToken'] = next_page
        response = client.list_users(**kwargs)
        users.extend(response['Users'])
        next_page = response.get('PaginationToken', None)
        users_remain = next_page is not None

    for user in users:
        justUserNames.append(user['Username'])

    return justUserNames


def read_users_from_file(path):
    users = []
    with open(path, "r", encoding="utf-8") as handle:
        for line in handle:
            username = line.strip()
            if username:
                users.append(username)
    return users


def delete_users_from_pool(userPoolUsers, userPoolId, region):
    client = boto3.client('cognito-idp', region_name=region)
    total = len(userPoolUsers)

    for index, user in enumerate(userPoolUsers, start=1):
        print(f"Deleting user {index}/{total}: {user}", file=sys.stderr)
        client.admin_delete_user(
            UserPoolId=userPoolId,
            Username=user
        )


def main():
    if len(sys.argv) == 3:
        userPoolUsers = get_user_pool_users(userPoolId, region)
        delete_users_from_pool(userPoolUsers, userPoolId, region)
        return

    if len(sys.argv) == 4 and sys.argv[3] == "--list-only":
        userPoolUsers = get_user_pool_users(userPoolId, region)
        for user in userPoolUsers:
            print(user)
        return

    if len(sys.argv) == 5 and sys.argv[3] == "--from-file":
        userPoolUsers = read_users_from_file(sys.argv[4])
        delete_users_from_pool(userPoolUsers, userPoolId, region)
        return

    raise SystemExit(
        "Usage: delete_users_from_user_pool.py <userPoolId> <region> "
        "[--list-only | --from-file <path>]"
    )


main()