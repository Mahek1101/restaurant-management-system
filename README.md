# Restaurant Reservation and Order Management System

## Project Overview

The Restaurant Reservation and Order Management System is a
database-driven web application developed using PHP, MySQL,
HTML, CSS, and JavaScript.

The system allows restaurant staff to manage customers,
reservations, food orders, and dining tables through a
user-friendly web interface.

It demonstrates relational database design, SQL queries,
backend API development, and CRUD operations.

## Technologies Used

- Frontend: HTML5, CSS3, JavaScript
- Backend: PHP
- Database: MySQL
- Database Connectivity: PHP MySQLi
- Web Server: Apache (XAMPP)
- Database Management: MySQL Workbench
- Development Environment: Visual Studio Code
- ER Diagram: dbdiagram.io
- Version Control: Git and GitHub

The application uses plain PHP and JavaScript without
a separate frontend or backend framework.

## Main Features

### Dashboard
- Displays total customers
- Displays total reservations
- Displays total dining tables
- Displays total food orders

### Customer Management
- Add customers
- View customers
- Update customer information
- Delete customers with confirmation

### Reservation Management
- Create reservations
- View reservations
- Update reservation details
- Delete reservations
- Validate dining table capacity
- Prevent overlapping reservations

### Food Order Management
- Create food orders
- View food orders
- Update food order information
- Delete food orders
- Assign existing servers and chefs

### Dining Table Management
- View dining table numbers
- View seating capacities
- View restaurant sections

## Database Design

The system uses a relational MySQL database named
`restaurant_management`.

It contains eight tables:

1. Customer
2. DiningTable
3. Employee
4. Server
5. Chef
6. Reservation
7. ReservationTable
8. FoodOrder

Primary keys and foreign keys maintain relationships
between the tables.

The ReservationTable table implements the many-to-many
relationship between reservations and dining tables.

The Employee table stores general employee information,
while Server and Chef store role-specific information.

## Backend APIs

The application provides three PHP API endpoints:

- `api/customers.php`
- `api/reservations.php`
- `api/orders.php`

Each endpoint supports:

- GET: Retrieve records
- POST: Create records
- PUT: Update records
- DELETE: Delete records

The APIs use prepared SQL statements for database writes
and return JSON responses.

## Installation Requirements

Install the following software:

1. PHP with the MySQLi extension
2. MySQL Server
3. Apache Web Server (XAMPP)
4. A web browser

MySQL Workbench is optional but useful for database inspection.

## Installation Instructions

### Step 1: Download the Project

Clone the GitHub repository or download it as a ZIP file.

Place the `restaurant_management` folder inside the
Apache web server's document root.

For a typical macOS XAMPP installation:

`/Applications/XAMPP/xamppfiles/htdocs/`

### Step 2: Start the Services

Start Apache using XAMPP.

Start your MySQL Server installation.

Ensure the MySQL server is accessible on port 3306,
or update the configuration to match your environment.

### Step 3: Import the Database

The database export is located at:

`database/restaurant_management.sql`

Open Terminal and run:

```bash
mysql -h 127.0.0.1 -P 3306 -u root -p < database/restaurant_management.sql