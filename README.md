# Symfony API Application

This repository contains a Symfony application set up with Docker, including services for Nginx, MySQL, and Swagger. The application processes card transactions via different payment gateways (Shift4 and ACI) and includes Swagger API documentation generated using NelmioApiDocBundle.

## Table of Contents

- [Installation](#installation)
- [Environment Setup](#environment-setup)
- [Docker Configuration](#docker-configuration)
- [Running the Application](#running-the-application)
- [API Documentation](#api-documentation)
- [Custom Commands](#custom-commands)
- [Running Tests](#running-tests)
- [Credits](#credits)

## Installation

1. **Clone the repository:**

    ```bash
    git clone https://github.com/wessama/mock-card-api
    cd mock-card-api
    ```

## Environment Setup

1. **Configure Environment Variables:**

   Copy the `.env` file to `.env.local` and adjust the environment variables as necessary, although `make setup` does this for you:

    ```bash
    cp .env .env.local
    ```

   Key environment variables:

    ```dotenv
    WORKER_PORT=8081#This is where you can access the API
    SWAGGER_PORT=8082
    SUPPORTED_PAYMENT_TYPES=shift4,aci
    MYSQL_ROOT_PASSWORD=root
    MYSQL_DATABASE=transactions_master
    MYSQL_PORT=3307
    DATABASE_URL="mysql://root:@mysql:3306/transactions_master?serverVersion=8.0.27"
    ```

## Docker Configuration

This project uses Docker to manage the development environment. It includes the following services:

- **worker**: PHP-FPM with Nginx
- **mysql**: MySQL database
- **swagger**: Swagger UI for API documentation

### Docker Setup

1. **Build and start the Docker containers:**

    ```bash
    make setup
    ```

   This command will:
    - Build the Docker images.
    - Start the containers.
    - Copy `.env` to `.env.local`.
    - Install Composer dependencies.
    - Run database migrations and load fixtures.
    - Generate JWT key pairs.

2. **Stop the containers:**

    ```bash
    make down
    ```

3. **Rebuild the containers:**

    ```bash
    make build
    ```

## Running the Application

The application should be accessible at:

- **API**: [http://localhost:8081](http://localhost:8081)
- **Swagger UI**: [http://localhost:8081/doc](http://localhost:8081/doc)

## API Documentation

The API is documented using Swagger and can be accessed through Swagger UI.

- **Access the API documentation**: [http://localhost:8081/doc](http://localhost:8081/doc)

### API Endpoints

- **Authenticate User**: `POST /api/login_check`

  **Request Body:**

    ```json
    {
        "username": "example@metricalo.com",
        "password": "password"
    }
    ```

- **Process Transaction**: `POST /api/transaction/{type}`

  **Request Body:**

    ```json
    {
        "amount": 150.75,
        "currency": "EUR",
        "card_holder_name": "Jane Jones",
        "card_number": "4200000000000000",
        "card_exp_month": "05",
        "card_exp_year": "2034",
        "card_cvv": "123"
    }
    ```

  `{type}` is a supported payment type: `shift4` or `aci`.

## Custom Commands

The application includes a custom command for processing a transaction.

For help with the command, run:

```bash
docker-compose exec worker php bin/console process-transaction --help
```

### Example

```bash
docker-compose exec worker php bin/console process-transaction shift4 \
    --amount=92.00 \
    --currency=EUR \
    --card-number=4200000000000000 \
    --card-holder="Jane Jones" \
    --card-exp-month=05 \
    --card-exp-year=2034 \
    --card-cvv=123
```

You can use `shift4` or `aci` as the transaction type.

## Running Tests

1. **Install PHPUnit** (if not already installed):

    ```bash
    make test
    ```

   This will run all the tests in the `tests/` directory.

## Credits

- [Symfony](https://symfony.com/)
- [Doctrine](https://www.doctrine-project.org/)
- LexikJWTAuthenticationBundle
- [Wessam Ahmed](mailto:wessam.ah@outlook.com)