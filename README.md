# Subscriber API

Welcome to the **Subscriber API** repository. This guide will help you set up, serve, and test your project with ease.

## Getting Started

To get started with the project, follow the steps below:

### 1. Install Dependencies

First, you need to install the required packages, such as PHPUnit and Guzzle. Run the following command in your terminal:

```bash
composer install
```

- **PHPUnit**: Used for writing and running tests.
- **Guzzle**: Allows interaction with API endpoints.

### 2. Navigate to Your Project Directory

Navigate to the root directory of your project by running:

```bash
cd /path/to/your/project
```

### 3. Serve Your Project

To serve your project locally, use the built-in PHP server:

```bash
php -S localhost:8000 -t public
```

Your project will now be accessible at `http://localhost:8000`.

### 4. PHP Version Requirement

This project was implemented using **PHP 8.3**. While older versions of PHP may work, PHP 8.3 is recommended for compatibility.

### 5. Set Correct Permissions

Ensure that the necessary permissions are set for your project files and directories.

### 6. Run Database Migrations

To set up the required database tables, run the SQL script located at `database/Migrations/subscribers-table.sql`. Make sure you created database. PostGresql for now.

### 7. Running Tests

#### Basic Test Execution

To run all the tests, use the following command:

```bash
./vendor/bin/phpunit
```

#### Running Specific Tests

If you want to run a specific test, for example, `testGetSubscriberSuccess`, you can do so by using:

```bash
./vendor/bin/phpunit --filter testGetSubscriberSuccess
```

### 8. UI Example for Testing

For a guide on how to test the application using the UI example, refer to the documentation in `node-http-client/ReadMe.md`.