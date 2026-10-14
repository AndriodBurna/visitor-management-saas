# Visitor Management System

A multi-organization visitor management system built with CodeIgniter 4 and MySQL. A Super Admin manages organizations, branches, and users, and staff check visitors in by organization, branch, and host, with a recorded visit history for every visitor.

## Features

**Authentication and access control**
- User login
- Role-protected pages
- Super Admin access for platform-wide administration
- Organization status is checked at login and during active sessions

**Organization management**
- Manage registered organizations and view organization details
- Control whether an organization can access the system (active or inactive)

**Branch and location management**
- Create, view, edit, and manage branches
- View branch details
- Each branch belongs to an organization

**User and host management**
- Manage system users and view user details
- Select a host during visitor registration

**Visitor management**
- All Visitors directory (platform-wide view for the Super Admin)
- Search visitors by identifying information
- Visitor details page: contact, company, organization, and identification details
- Visitor photo area (shows the photo if one exists, otherwise an empty state)
- Visit history: branch, host, purpose, status, check-in time, check-out time, and notes

**New Check-In**
- Select the organization receiving the visitor
- Branch options load for the selected organization
- Select a host, or leave the host unspecified
- Capture the visitor's name, phone, email, and company
- Capture identification type and number
- Capture the purpose of the visit and additional notes
- "Check in visitor" submission

## Current status

This project is in-active development. Host selection and visitor search are working foundations: the organization and branch relationships that will fully control host availability, and richer search filters, are still to come.

## Built with

PHP, CodeIgniter 4, MySQL, HTML, CSS, JavaScript. The layout is responsive, using CSS media queries. Developed locally with XAMPP.

## Getting started

1. Clone the repository.
2. Install dependencies with `composer install`.
3. Copy `env` to `.env` and set your database details.
4. Create a MySQL database. Import the SQL file if the repository includes one, or run `php spark migrate` if it uses migrations.
5. Start the app with XAMPP, or run `php spark serve`.

## Author

Emmanuel Kalibbala · [github.com/AndriodBurna](https://github.com/AndriodBurna)
