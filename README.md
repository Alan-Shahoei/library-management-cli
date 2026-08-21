<div align="center">

# 📚 Library Management CLI

**A lightweight library management system for the command line**

Manage books, physical copies, members, and loans with an interactive numeric menu — no database or framework required.

[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![Composer](https://img.shields.io/badge/Composer-PSR--4-885630?style=for-the-badge&logo=composer&logoColor=white)](https://getcomposer.org/)
![JSON Storage](https://img.shields.io/badge/Storage-JSON-20232A?style=for-the-badge&logo=json&logoColor=white)
[![MIT License](https://img.shields.io/badge/License-MIT-2EA44F?style=for-the-badge)](LICENSE)

`PHP 8.2+` · `JSON Storage` · `Layered Architecture` · `Interactive CLI`

</div>

---

## Overview

Library Management CLI handles the essential operations of a small library through an interactive numeric menu. It manages book records, physical copies, members, and loans while storing all data in JSON files.

The project separates business rules from the command-line interface and file storage. Domain models describe the library, application services coordinate use cases, repository interfaces define persistence contracts, and JSON repositories handle reading and writing data.

## Features

|    | Capability                                                            |
|:--:|-----------------------------------------------------------------------|
| 📖 | Add and update books with multiple authors and an optional edition    |
| 🔢 | Generate sequential book and member IDs such as `B00001` and `M00001` |
| 🏷️ | Register physical book copies using unique barcodes                   |
| 👤 | Register, update, activate, and deactivate members                    |
| 🔄 | Borrow and return individual book copies                              |
| 📋 | List all, active, overdue, or member-specific loans                   |
| 🕘 | Preserve the history of returned loans                                |
| 💾 | Persist all data in JSON files without a database                     |

## 🧱 Architecture

| Layer             | Responsibility                                                             |
|-------------------|----------------------------------------------------------------------------|
| `Domain`          | Contains `Book`, `BookCopy`, `Member`, `Loan`, and repository interfaces   |
| `Application`     | Coordinates library use cases and enforces business rules through services |
| `Infrastructure`  | Implements repository contracts using JSON files                           |
| `Presentation`    | Displays menus, reads user input, and prints results                       |
| `bin/library.php` | Creates the dependencies and starts the application                        |

> [!NOTE]
> `Book` represents shared catalog information. `BookCopy` represents one physical copy with its own barcode, so several copies of the same book can be borrowed independently.

## 🗂️ Project Structure

```text
library-management-cli/
├── bin/
│   └── library.php
├── src/
│   ├── Application/
│   │   └── Service/
│   │       ├── BookService.php
│   │       ├── LoanService.php
│   │       └── MemberService.php
│   ├── Domain/
│   │   ├── Repository/
│   │   ├── Book.php
│   │   ├── BookCopy.php
│   │   ├── Loan.php
│   │   └── Member.php
│   ├── Infrastructure/
│   │   └── Persistence/
│   │       └── Json/
│   └── Presentation/
│       └── Cli/
│           └── ConsoleApplication.php
├── storage/
├── .gitignore
├── composer.json
├── composer.lock
├── LICENSE
└── README.md
```

## Getting Started

### Requirements

- PHP 8.2 or newer
- Composer 2.x
- A command-line terminal

### 1. Install

```bash
git clone https://github.com/Alan-Shahoei/library-management-cli.git
cd library-management-cli
composer install
```

### 2. Run

```bash
php bin/library.php
```

> [!IMPORTANT]
> Run the application in a terminal. It reads interactive input from `STDIN` and is not intended for a browser or PHP preview window.

## 🖥️ Using the Application

The application starts with this main menu:

```text
=== Library Management ===
1. Book management
2. Member management
3. Loan management
0. Exit
Select an option:
```

| Menu                  | Available actions                                                                                   |
|-----------------------|-----------------------------------------------------------------------------------------------------|
| **Book Management**   | Add or update a book, add a physical copy, activate or deactivate a copy, and list books and copies |
| **Member Management** | Register or update a member, activate or deactivate a member, and list all members                  |
| **Loan Management**   | Borrow or return a copy and list all, active, overdue, or member-specific loans                     |

Enter `0` to return from a submenu or exit from the main menu.

## 📋 Library Rules

| Rule                | Behavior                                          |
|---------------------|---------------------------------------------------|
| Loan duration       | 14 days                                           |
| Active loan limit   | Maximum 3 per member                              |
| Duplicate books     | The same title, authors, and edition are rejected |
| Copy barcode        | Must be unique                                    |
| Borrowing           | The member and book copy must both be active      |
| Copy availability   | A copy cannot have more than one active loan      |
| Member deactivation | Blocked while the member has active loans         |
| Copy deactivation   | Blocked while the copy has an active loan         |

## 💾 How Persistence Works

Each entity type has its own repository and JSON file. Repositories convert JSON records into Domain objects when reading and convert the objects back into arrays when saving.

| File                       | Stored data               |
|----------------------------|---------------------------|
| `storage/books.json`       | Book records              |
| `storage/book-copies.json` | Physical copy records     |
| `storage/members.json`     | Member records            |
| `storage/loans.json`       | Active and returned loans |

The storage directory and files are created automatically when data is first saved. Generated JSON files are ignored by Git.

## 🛠️ Tech Stack

| Technology   | Usage                                       |
|--------------|---------------------------------------------|
| **PHP 8.2+** | Application and domain logic                |
| **Composer** | Dependency management and PSR-4 autoloading |
| **JSON**     | File-based data persistence                 |

The application uses no database, framework, or third-party runtime package.

## 📄 License

This project is available under the [MIT License](LICENSE).

## 👤 Author

Created by [Alan Shahoei](https://github.com/Alan-Shahoei).
