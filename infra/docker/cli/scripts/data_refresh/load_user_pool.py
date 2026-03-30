#!/opt/venv/bin/python

import sys
import os
import boto3
import string
import random
import csv
import json
import re

emailRegex = re.compile(r'^([A-Za-z0-9]+[.-_])*[A-Za-z0-9]+@[A-Za-z0-9-]+(\.[A-Z|a-z]{2,})+$')

# Usage: script userPoolId region knownPasswordId defaultEmail
userPoolId = sys.argv[1]
region = sys.argv[2]
knownPasswordId = sys.argv[3]
defaultEmail = sys.argv[4]

# Get environment from batch/container variable
environment = os.environ.get("BATCH_ENVIRONMENT", "").lower()
allowed_environments = ["dev", "int", "pp", "app"]
if environment not in allowed_environments:
    raise Exception(f"Unrecognized environment: {environment}. Allowed: {allowed_environments}")

userPoolUsers = []
csvFileUsers = []

csvFile = f"users-{environment}.txt"
uatUsersCsvFile = "uat-users.txt"

def get_known_password(environment_key, secretId, region):
    client = boto3.client('secretsmanager', region_name=region)
    response = client.get_secret_value(SecretId=secretId)
    secrets = json.loads(response['SecretString'])
    key = environment_key + "_known_password"
    return secrets[key]

def get_user_pool_users(userPoolId, region):
    users = []
    justUserNames = []
    client = boto3.client('cognito-idp', region_name=region)
    next_page = None
    kwargs = {'UserPoolId': userPoolId}
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

def get_csv_file_users(csvFile):
    with open(csvFile, 'r') as csvfile:
        csvUsers = list(csv.reader(csvfile, delimiter='|'))
    return csvUsers

def get_random_password():
    letters_lower = string.ascii_lowercase
    letters_upper = string.ascii_uppercase
    numbers = string.digits
    randomPassword = ''.join(random.choice(letters_lower) for _ in range(5))
    randomPassword += ''.join(random.choice(letters_upper) for _ in range(5))
    randomPassword += ''.join(random.choice(numbers) for _ in range(10))
    return ''.join(random.sample(randomPassword, len(randomPassword)))

def load_users_into_pool(csvFileUsers, userPoolUsers, userPoolId, secretKnownPassword, defaultEmail, permanent):
    client = boto3.client('cognito-idp', region_name=region)
    for user in csvFileUsers:
        if user[0].lower() in userPoolUsers:
            print(f"Found user {user[0].lower()}, resetting password to known default")
        else:
            randomPassword = get_random_password()
            print(f"Adding user {user[0].lower()}")
            if user[1] == "" or not re.fullmatch(emailRegex, user[1]):
                print(f"User {user[0].lower()} has no email address or is invalid - using {defaultEmail}")
                user[1] = defaultEmail
            client.admin_create_user(
                UserPoolId=userPoolId,
                Username=user[0].lower(),
                UserAttributes=[
                    {'Name': 'email', 'Value': user[1]},
                    {'Name': 'email_verified', 'Value': 'true'}
                ],
                TemporaryPassword=randomPassword,
                MessageAction='SUPPRESS'
            )
        knownPassword = get_random_password() if secretKnownPassword == "random" else secretKnownPassword
        client.admin_set_user_password(
            UserPoolId=userPoolId,
            Username=user[0].lower(),
            Password=knownPassword,
            Permanent=permanent
        )

userPoolUsers = get_user_pool_users(userPoolId, region)
csvFileUsers = get_csv_file_users(csvFile)

if environment == "dev":
    # dev: known password for all users
    knownPassword = get_known_password("nonprod", knownPasswordId, region)
    load_users_into_pool(csvFileUsers, userPoolUsers, userPoolId, knownPassword, defaultEmail, True)

elif environment == "int":
    # int: known password for all users and for UAT testers
    csvUatFileUsers = get_csv_file_users(uatUsersCsvFile)
    knownPassword = get_known_password("int", knownPasswordId, region)
    load_users_into_pool(csvFileUsers, userPoolUsers, userPoolId, knownPassword, defaultEmail, True)
    userPoolUsers = get_user_pool_users(userPoolId, region)
    load_users_into_pool(csvUatFileUsers, userPoolUsers, userPoolId, knownPassword, defaultEmail, True)

elif environment == "pp":
    # pp: random password for users, known password for UAT testers
    csvUatFileUsers = get_csv_file_users(uatUsersCsvFile)
    knownPassword = get_known_password("pp", knownPasswordId, region)
    load_users_into_pool(csvFileUsers, userPoolUsers, userPoolId, "random", defaultEmail, True)
    userPoolUsers = get_user_pool_users(userPoolId, region)
    load_users_into_pool(csvUatFileUsers, userPoolUsers, userPoolId, knownPassword, defaultEmail, True)

elif environment == "app":
    # app: load users with random password, not permanent to force reset
    load_users_into_pool(csvFileUsers, userPoolUsers, userPoolId, "random", defaultEmail, False)
