# Travel Agency API

This is a RESTful API for managing travel orders, including user authentication, travel order creation, search with multiple parameters,
status updates, mail notification, and more. The API is designed for use by travel agencies and their clients, with full 
documentation provided below. 

In this Laravel API example, we demonstrate the use of multiple Laravel features to build an API, including Routes, Resources, Middlewares, Policies, Eloquent ORM, Enums, Mailables, and more.

## Table of Contents
1. [Installation](#installation)
2. [Running Locally](#running-locally)
3. [Testing](#testing)
4. [Postman Testing](#postman-testing)
5. [API Endpoints Documentation](#api-endpoints-documentation)
6. [Filters for Travel Order Search](#filters-for-travel-order-search)

---

## Installation



1. **Clone the Repository**:
    ```bash
    git clone git@github.com:33Piter/travel-agency-api.git
    cd travel-agency-api
    ```

2. **Automated Installation (preferred)**:
 - Run the installation script:
    ```bash
   chmod +x install.sh
   ./install.sh
    ```
- _Optionally, run the script with --skip-tests to skip running tests._

3. **Manual Installation**:
- Install dependencies:

  ```bash
  composer install
  ```

- Copy the example environment configuration:
   ```bash
   cp .env.example .env
   ```

- Start Docker containers with Laravel Sail:
   ```bash
   ./vendor/bin/sail up -d
   ```
  
- Generate JWT secret:
   ```bash
   ./vendor/bin/sail artisan jwt:secret --force
   ```

- Run tests (optional):
   ```bash
   ./vendor/bin/sail artisan test
   ```

---

## Running Locally

1. **Run the Application (it is already running if you follow the installation steps)**:
- To start the application, use:
   ```bash
   ./vendor/bin/sail up -d
   ```

- To stop the application, use:
   ```bash
   ./vendor/bin/sail down
   ```

2. **Optional Migration and Seeding:**
- If you wish to populate the database with some fake users and travel orders, run migrations and seed:
   ```bash
   ./vendor/bin/sail artisan migrate --seed
   ```
By default, the applications runs on localhost port 7001. If you want to change the port, 
you can do so by editing the APP_PORT variable in the .env file.

---

## Testing

The application is covered by multiple tests. To run all tests, use the following command:
- **Run all tests**:
    ```bash
    ./vendor/bin/sail artisan test
    ```
---

## Postman Testing

A Postman collection is included in the repository to simplify testing:
1. **Import the Collection**:
   Open Postman, go to "File > Import," and import the collection file located at `docs\Travel Agency API.postman_collection.json` in the repository.

2. **Base URL**:
   The default base url variable is set to 'http://localhost:7001/api/v1'. Change it if you are running the application on a different port.

3. **Authentication**:
    - Start with the `Register User` or `Login` endpoints to obtain an authentication token.
    - Add the `Authorization` header with the token for secured endpoints.

---

## API Endpoints Documentation

| Endpoint                      | HTTP Method | Description                                                                                                                                                          | Authentication Required |
|-------------------------------|-------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------|--------------------------|
| `/auth/register`              | POST        | Register a new user. No authentication required for this action.                                                                                                     | No                       |
| `/auth/login`                 | POST        | Login a user and retrieve the authentication token.                                                                                                                  | No                       |
| `/auth/logout`                | GET         | Logout the user and invalidate the authentication token.                                                                                                             | Yes                      |
| `/auth/refresh`               | GET         | Refresh the authentication token to extend the user's session.                                                                                                       | Yes                      |
| `/auth/user`                  | GET         | Retrieve the authenticated user's information.                                                                                                                       | Yes                      |
| `/travel-order`               | POST        | Create a new travel order. Only the authenticated user can create orders.                                                                                            | Yes                      |
| `/travel-order/{id}`          | GET         | Retrieve a specific travel order by ID. This will only return the order if the authenticated user owns it.                                                           | Yes                      |
| `/travel-order`               | GET         | Search for travel orders. This will only show travel orders that the authenticated user owns. The results are paginated (10 per page). See all search filters below. | Yes                      |
| `/travel-order/{id}?status`   | PUT         | Update a specific travel order by ID. The user can only update orders they own.                                                                                      | Yes                      |
| `/travel-order/notify/{id}`   | GET         | Notify the user associated with a travel order via email. This action is only permitted if the user owns the travel order.                                           | Yes                      |


## Filters for Travel Order Search

- **Departure date range**

```departure_date_start```: Enter the start of the date range (format: YYYY-MM-DD).

```departure_date_end```: Enter the end of the date range (format: YYYY-MM-DD, must be after or equal to ```departure_date_start```).

- **Return date range**

```return_date_start```: Enter the start of the date range (format: YYYY-MM-DD).

```return_date_end```: Enter the end of the date range (format: YYYY-MM-DD, must be after or equal to ```return_date_start```).

- **Date range (both departure and return)**

```date_range_start```: Enter the start of the date range (format: YYYY-MM-DD).

```date_range_end```: Enter the end of the date range (format: YYYY-MM-DD, must be after or equal to ```date_range_start```).

- **Status**

```status```: Filter by travel order status. It must be one of the following values: 'requested', 'approved' or 'canceled'.

- **Destination**

```destination```: Filter by the destination of the travel order.



