# API SPECIFICATION

## Authentication

### Auth user

Request:
- Method: POST
- Endpoint: `auth.php`
- Header: 
    - Content-Type: application/json
    - Accept: application/json
- Body:
```json
{
    "username": "string",
    "password": "string",
}
```

Response:
```json
{
    "code": "number",
    "status": "string",
    "data": {
        "token": "string,
    },
}
```

## Email

### Send email

Request:
- Method: POST
- Endpoint: `email.php`
- Header: 
    - Content-Type: application/json
    - Accept: application/json
- Body:
```json
{
    "token": {
        "type": "string[user_token]",
        "token": "string",
    },
    "data": {
        "recepient_email": "string",
        "html": "string",
        "raw": "string",
        "subject": "string",
    },
}
```

Response:
```json
{
    "code": "number",
    "status": "string",
    "data": {
        "id": "int",
        "recepient_email": "string",
        "html": "string",
        "raw": "string",
        "subject": "string",
    },
}
```

### Email Callback (from server to user's callback url)

Request:
- Method: POST
- Endpoint: $callbackUrl
- Header: 
    - Content-Type: application/json
    - Accept: application/json
- Body:
```json
{
    "secret": "string",
    "data": {
        "id": "int",
        "recepient_email": "string",
        "html": "string",
        "raw": "string",
        "subject": "string",
    },
}
```

