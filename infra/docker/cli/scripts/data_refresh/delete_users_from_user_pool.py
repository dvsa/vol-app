#!/usr/bin/python3

import sys
import boto3
import botocore

# ${userPoolId}" "${params.Region}"
userPoolId = sys.argv[1]
region = sys.argv[2]

userPoolUsers = list ()

def get_user_pool_users(userPoolId, region):
    users = list ()
    justUserNames = []

    client = boto3.client('cognito-idp', region_name=region)

    next_page = None
    kwargs = {
        'UserPoolId': userPoolId
    }

    users_remain = True
    while(users_remain):
        if next_page:
            kwargs['PaginationToken'] = next_page
        response = client.list_users(**kwargs)
        users.extend(response['Users'])
        next_page = response.get('PaginationToken', None)
        users_remain = next_page is not None


    for user in users:
        justUserNames.append(user['Username'])

    return justUserNames

def delete_users_from_pool(userPoolUsers, userPoolId):
    client = boto3.client('cognito-idp', region_name=region)

    for user in userPoolUsers:
        print("Deleting user " + user)
        client.admin_delete_user(
            UserPoolId = userPoolId,
            Username = user
        )

userPoolUsers = get_user_pool_users(userPoolId, region)
delete_users_from_pool(userPoolUsers, userPoolId)
