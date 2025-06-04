# UKM Finance Management

A web application for managing the finances of student activity units (UKM) at a university. This application facilitates financial record-keeping, enhances transparency in financial reporting, and minimizes recording errors.

## Project Overview

- **Purpose:** The application is designed to help UKM administrators and treasurers manage financial transactions, generate reports, and maintain financial transparency.
- **Technologies Used:** PHP, MySQL, XML, and RESTful APIs.

## Domains Used

### 1. Local Domain (Primary Application)
- This is where the main web application runs (e.g., `http://localhost/ukm_finance` or your server's domain).
- It handles user interface, authentication, transaction management, and local API endpoints.
- Local API endpoints are located in `ukm_finance/api/` and are used for internal data operations and serving data to the frontend.

### 2. External Domain (API/Data Exchange)
- The application is integrated with an external domain for API and data exchange:
  - Example: `https://ukmfinancepraditas.infinityfreeapp.com/api/`
- This domain is used for:
  - Exchanging transaction data between different systems.
  - Acting as a proxy for certain API requests (see `ukm_finance/Domain 2(For External API)/proxy.php`).
  - Allowing cross-domain data sharing, such as exporting/importing transactions or reports.
- The local application can send or receive data from this external API, and some endpoints (like `proxy.php`) are specifically designed to forward requests and handle responses from the external domain.

## How Data Exchange Works

- The local application can make API calls to the external domain for operations like fetching or updating transactions.
- The external domain can also call back to the local API for data synchronization.
- CORS headers are set up to allow cross-domain requests (see `ukm_finance/api/config.php`).

## Features

- Recording financial transactions (income and expenses)
- Automatic financial reporting with data visualization
- Multi-UKM management
- Data export to XML format
- User login and management system

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

## Installation

1. **Clone or download this repository**

2. **Import the database**
   - Create a new database in MySQL
   - Import the file `database/ukm_finance.sql` into the created database

3. **Configure the database**
   - Open the file `class/finance.php`
   - Adjust the database configuration:
     ```php
     private $host = 'localhost'; // Adjust to your database host
     private $user = 'root';      // Adjust to your database username
     private $pass = '';          // Adjust to your database password
     private $database = "ukm_finance"; // Adjust to your database name
     ```

4. **Access the application**
   - Move the `ukm_finance` folder to your web server directory
   - Access via browser: `http://localhost/ukm_finance`

## Usage

### Login
- Use the provided email and password:
  - Admin: admin@example.com / password123
  - Treasurer UKM Sports: budi@example.com / password123
  - Treasurer UKM Music: dewi@example.com / password123
  - Treasurer UKM Photography: andi@example.com / password123
  - Treasurer UKM Journalism: siti@example.com / password123
  - Treasurer UKM Nature Lovers: rudi@example.com / password123

### Dashboard Page
- View UKM financial summary
- View financial charts
- View recent transactions

### Transaction Page
- Add new transactions
- View transaction list
- Search and filter transactions
- Delete transactions

### Financial Report Page
- View detailed financial reports
- View distribution charts by category
- View financial trends for the last 6 months
- Export data to XML
- Print reports

## Folder Structure

- `api/` - Contains API files for database communication
- `class/` - Contains PHP classes for business logic
- `database/` - Contains SQL files for database setup
- `images/` - Contains images and visual assets
- `inc/` - Contains include files such as header and footer
- `js/` - Contains JavaScript files
- `style/` - Contains CSS files

## Contribution

If you wish to contribute to this project, please fork the repository and submit a pull request.

## License

This project is licensed under the [MIT License](LICENSE).
