#!/opt/venv/bin/python

import sys
import boto3

# ${userPoolId}" "${params.Region}"
userPoolId = sys.argv[1]
region = sys.argv[2]

CLIENT_REFRESH_EVERY = 50


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


def delete_users_from_pool(userPoolUsers, userPoolId, region, refresh_every=CLIENT_REFRESH_EVERY):
    client = None
    total = len(userPoolUsers)

    for index, user in enumerate(userPoolUsers, start=1):
        if client is None or (index - 1) % refresh_every == 0:
            print(f"Creating Cognito client at user {index}/{total}")
            client = boto3.client('cognito-idp', region_name=region)

        print(f"Deleting user {index}/{total}: {user}")
        client.admin_delete_user(
            UserPoolId=userPoolId,
            Username=user
        )


userPoolUsers = get_user_pool_users(userPoolId, region)
delete_users_from_pool(userPoolUsers, userPoolId, region)