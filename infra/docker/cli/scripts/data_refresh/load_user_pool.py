#!/usr/bin/python3

import sys
import boto3
import botocore

import string
import random

import csv
import json

import re

emailRegex = re.compile(r'^([A-Za-z0-9]+[.-_])*[A-Za-z0-9]+@[A-Za-z0-9-]+(\.[A-Z|a-z]{2,})+$')

# ${userPoolId}" "${params.Region}" ${envirnonment} ${known_password_id} ${env.default_email}
userPoolId = sys.argv[1]
region = sys.argv[2]
environment = sys.argv[3]
knownPasswordId = sys.argv[4]
defaultEmail = sys.argv[5]

userPoolUsers = list ()
csvFileUsers = []

csvFile = "users-" + environment + ".txt"
uatUsersCsvFile = "uat-users.txt"

def get_known_password(environment_key, secretId, region):
    client = boto3.client('secretsmanager', region_name=region)

    response = client.get_secret_value(
        SecretId = secretId
    )
    secrets = json.loads(response['SecretString'])

    key = environment_key + "_known_password"

    return secrets[key]

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

def get_csv_file_users(csvFile):
    with open(csvFile, 'r') as csvfile:
        csvUsers = list(csv.reader(csvfile, delimiter='|'))
    return csvUsers

def get_random_password():
    letters_lower = string.ascii_lowercase
    letters_upper = string.ascii_uppercase
    numbers = string.digits
    # 10 upper and lower letters
    randomPassword = ''.join(random.choice(letters_lower) for i in range(5))
    randomPassword = randomPassword + ''.join(random.choice(letters_upper) for i in range(5))
    # 10 numbers
    randomPassword = randomPassword + ''.join(random.choice(numbers) for i in range(10))
    # and shuffle them
    randomPassword = ''.join(random.sample(randomPassword,len(randomPassword)))
    return randomPassword

def load_users_into_pool(csvFileUsers, userPoolUsers, userPoolId, secretKnownPassword, defaultEmail, permenant):
    client = boto3.client('cognito-idp', region_name=region)

    # Each line seems to be [0]=username, [1]=email
    for user in csvFileUsers:
        if user[0].lower() in userPoolUsers:
            print("Found user " + user[0].lower() + ", resetting password to known default")
        else:
            randomPassword = get_random_password()
            print("Adding user " + user[0].lower())
            if user[1] == "" or not re.fullmatch(emailRegex, user[1]):
                print("User " + user[0].lower() + " has no email address or is invalid - using " + defaultEmail)
                user[1] = defaultEmail

            client.admin_create_user(
              UserPoolId = userPoolId,
              Username = user[0].lower(),
              UserAttributes = [
                  {
                      'Name': 'email',
                      'Value': user[1]
                  },
                  {
                      'Name': 'email_verified',
                      'Value': 'true'
                  }
              ],
              TemporaryPassword = randomPassword,
              MessageAction = 'SUPPRESS'
            )
        if secretKnownPassword == "random":
            randomPassword = get_random_password()
            knownPassword = randomPassword
        else:
            knownPassword = secretKnownPassword

        client.admin_set_user_password(
            UserPoolId = userPoolId,
            Username = user[0].lower(),
            Password = knownPassword,
            Permanent = permenant
        )

userPoolUsers = get_user_pool_users(userPoolId, region)
csvFileUsers = get_csv_file_users(csvFile)

# match statements only came in at python 3.10
if environment == "dev" or environment == "reg" or environment == "da" or environment == "qa" or environment == "demo" or environment == "prodsupp":
    knownPassword = get_known_password("nonprod", knownPasswordId, region)
    # nonprod only needs known passwords for users
    load_users_into_pool(csvFileUsers, userPoolUsers, userPoolId, knownPassword, defaultEmail, True)

if environment == "int":
    csvUatFileUsers = get_csv_file_users(uatUsersCsvFile)
    knownPassword = get_known_password(environment, knownPasswordId, region)
    # int needs known passwords for users
    load_users_into_pool(csvFileUsers, userPoolUsers, userPoolId, knownPassword, defaultEmail, True)
    # Reload the user pool now it has the DB extracted users in it
    userPoolUsers = get_user_pool_users(userPoolId, region)
    # And known passwords for UAT testers
    load_users_into_pool(csvUatFileUsers, userPoolUsers, userPoolId, knownPassword, defaultEmail, True)

if environment == "pp":
    csvUatFileUsers = get_csv_file_users(uatUsersCsvFile)
    knownPassword = get_known_password(environment, knownPasswordId, region)
    # pp needs random passwords for users
    load_users_into_pool(csvFileUsers, userPoolUsers, userPoolId, "random", defaultEmail, True)
    # Reload the user pool now it has the DB extracted users in it
    userPoolUsers = get_user_pool_users(userPoolId, region)
    # But known passwords for UAT testers
    load_users_into_pool(csvUatFileUsers, userPoolUsers, userPoolId, knownPassword, defaultEmail, True)

if environment == "app":
    # app just needs load users wih random password, but be in non permenant mode to force reset password
    load_users_into_pool(csvFileUsers, userPoolUsers, userPoolId, "random", defaultEmail, False)
