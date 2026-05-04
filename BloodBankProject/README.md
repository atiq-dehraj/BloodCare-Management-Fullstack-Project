# 🩸 BloodCare — Blood Bank Management System

> A full-stack three-tier web application for managing blood bank operations, donor records, hospital requests, and inventory tracking.

**Live Demo:** [http://bloodcare.kesug.com](http://bloodcare.kesug.com)

---

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [System Architecture](#system-architecture)
- [Database Design](#database-design)
- [Project Structure](#project-structure)
- [Getting Started](#getting-started)
- [Environment Setup](#environment-setup)
- [Portals & Access](#portals--access)
- [Screenshots](#screenshots)

---

## Overview

BloodCare addresses critical gaps in traditional blood bank operations:

- **Inventory Tracking** — Real-time monitoring of blood bag status, expiration dates, and stock levels across storage units.
- **Request Fulfillment** — Streamlined hospital request processing with urgency handling.
- **Donor Management** — Safe donation interval tracking and complete donor history.

---

## Features

| Module | Description |
|---|---|
| 🔐 Login System | Role-based authentication for Admin, Staff, Donor, and Hospital users |
| 👤 Donor Portal | Register donors, view donation history, schedule appointments |
| 🏥 Hospital Portal | Submit blood requests, track fulfillment status |
| 🧑‍⚕️ Staff Portal | Log donations, update blood bag status, manage storage |
| 📊 Manager Portal | Dashboard analytics, reports, full system oversight |
| 🔧 Admin Portal | User management, system configuration |

---

## Tech Stack

- **Frontend:** HTML5, CSS3, JavaScript
- **Backend:** PHP (with Composer for dependency management)
- **Database:** MySQL
- **Server:** Apache (via XAMPP / shared hosting)

---

## System Architecture

```
┌─────────────────────────────────────────────┐
│                  Frontend                   │
│         (HTML / CSS / JavaScript)           │
└────────────────────┬────────────────────────┘
                     │ HTTP Requests
┌────────────────────▼────────────────────────┐
│                PHP Backend                  │
│  (Portal Controllers + Business Logic)      │
└────────────────────┬────────────────────────┘
                     │ SQL Queries
┌────────────────────▼────────────────────────┐
│             MySQL Database                  │
│            (bloodcare_db)                   │
└─────────────────────────────────────────────┘
```

---

## Database Design

The system uses a normalized relational schema with the following core entities:

- **Donor** — `DonorID`, Name, BloodType, Phone, LastDonationDate
- **Donation** — `DonationID`, Date, Volume, linked to Donor & Staff
- **Staff** — `StaffID`, Name, Role
- **BloodBag** — `BagID`, BloodType, ExpirationDate, Status (`Available` / `Quarantined` / `Dispatched` / `Expired`)
- **StorageUnit** — `BloodID`, Location, Temperature, Status
- **Hospital** — `HospitalID`, Name, Location
- **Request** — `RequestID`, BloodTypeRequired, Urgency, Location, ContactPerson
- **Transaction** — Links BloodBags to Requests (M:N bridge table)

### Key Relationships

```
Donor     (1) ──── MAKES ──── (N) Donation
Staff     (1) ── SUPERVISES ── (N) Donation
Donation  (1) ─── PRODUCES ── (N) BloodBag
BloodBag  (N) ── STORAGE IN ── (1) StorageUnit
BloodBag  (N) ─── FULFILLS ─── (M) Request  [via Transaction]
Hospital  (1) ──── MADE BY ─── (M) Request
```

---

## Project Structure

```
bloodcare/
├── admin_portal/          # Admin user management & config
├── backend/               # PHP logic (CRUD, reports, auth)
│   └── generate_report.php
├── database/              # DB utilities
├── donor_portal/          # Donor-facing pages
├── hospital_portal/       # Hospital request & tracking pages
├── login_system/          # Authentication & session handling
├── manager_portal/        # Dashboard & analytics
├── staff_portal/          # Donation logging & storage
├── vendor/                # Composer dependencies
├── front.html             # Landing page
├── front.css              # Global styles
├── front.js               # Frontend scripts
├── composer.json          # PHP dependency manifest
├── bloodcare_db.sql       # Full database schema & seed data
└── .env.example           # Environment variable template
```

---

## Getting Started

### Prerequisites

- PHP >= 7.4
- MySQL >= 5.7
- Apache (XAMPP recommended for local development)
- Composer

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/YOUR_USERNAME/bloodcare.git
cd bloodcare

# 2. Install PHP dependencies
composer install

# 3. Copy environment config
cp .env.example .env
# Edit .env with your database credentials

# 4. Import the database
mysql -u root -p < bloodcare_db.sql

# 5. Start your local server (XAMPP/WAMP)
#    Point document root to the project folder
#    Visit: http://localhost/bloodcare/front.html
```

---

## Environment Setup

Copy `.env.example` to `.env` and fill in your values:

```env
DB_HOST=localhost
DB_NAME=bloodcare_db
DB_USER=root
DB_PASS=your_password
```

> ⚠️ **Never commit your `.env` file.** It is listed in `.gitignore`.

---

## Portals & Access

| Portal | URL Path | Default Role |
|---|---|---|
| Landing Page | `/front.html` | Public |
| Login | `/login_system/` | All |
| Donor Portal | `/donor_portal/` | Donor |
| Staff Portal | `/staff_portal/` | Staff |
| Hospital Portal | `/hospital_portal/` | Hospital |
| Manager Portal | `/manager_portal/` | Manager |
| Admin Portal | `/admin_portal/` | Admin |

---

## License

This project was developed as an academic project. All rights reserved.
