<div align="center">

# 🚀 NovaCMS

### Modern Headless CMS & Visual Website Builder

Build, manage, and publish modern websites through a flexible component-based architecture.

> **Currently under active development.**

![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel)
![Filament](https://img.shields.io/badge/Filament-v4-F59E0B?style=for-the-badge)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-17-336791?style=for-the-badge&logo=postgresql)
![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker)
![License](https://img.shields.io/github/license/Fikrisitorus/NovaCMS?style=for-the-badge)

</div>

---

# 📖 Overview

NovaCMS is a modern **Headless CMS** designed to simplify website management using a modular, component-driven architecture.

Unlike traditional CMS platforms, NovaCMS separates content management from frontend rendering, allowing developers to build websites with complete flexibility while giving content editors an intuitive administration experience.

This project is built with scalability, maintainability, and developer experience in mind.

---

# ✨ Features

## Core CMS

- Website Management
- Multi Page Management
- Dynamic Sections
- Blog System
- Media Library
- SEO Management
- Draft & Publish
- Version History

## Content Builder

- Hero Section
- Feature Section
- Gallery
- FAQ
- Pricing
- CTA
- Team
- Contact Form
- Custom Components

## Administration

- Authentication
- Role & Permission
- User Management
- Activity Log
- Dashboard Analytics

## Developer Experience

- REST API
- Docker Ready
- Queue
- Cache
- Search
- CI/CD
- Modular Architecture
- Clean Architecture
- Domain Driven Design

---

# 🏗 Project Structure

```text
NovaCMS
│
├── apps
│   ├── api
│   └── website
│
├── packages
│
├── docs
│
├── docker
│
├── design
│
├── scripts
│
└── .github
```

---

# 🏛 Architecture

NovaCMS follows several modern software architecture principles.

- Modular Monolith
- Domain Driven Design (DDD)
- Clean Architecture
- Repository Pattern
- SOLID Principles
- Event Driven Design

---

# 🛠 Technology Stack

## Backend

- Laravel 12
- PHP 8.4
- PostgreSQL
- Redis

## Admin Panel

- Filament v4

## Frontend

- Blade
- Livewire
- TailwindCSS
- Alpine.js

## Infrastructure

- Docker
- Nginx
- MinIO
- Mailpit
- Meilisearch

---

# 📁 Repository Layout

```text
apps/
    api/
    website/

packages/
docs/
docker/
design/
scripts/
```

---

# 🚀 Getting Started

Clone repository

```bash
git clone https://github.com/Fikrisitorus/NovaCMS.git
```

Move into project

```bash
cd NovaCMS
```

Start Docker

```bash
docker compose up -d
```

Install dependencies

```bash
composer install
```

Generate application key

```bash
php artisan key:generate
```

Run migration

```bash
php artisan migrate
```

Run development server

```bash
php artisan serve
```

---

# 📚 Documentation

Documentation is available inside the `docs` directory.

- Product Requirement
- Architecture
- API Specification
- Database Design
- Deployment Guide
- Development Roadmap

---

# 🗺 Roadmap

## Phase 1

- Authentication
- User Management
- Role & Permission

## Phase 2

- Website
- Pages
- Sections

## Phase 3

- Media Library
- Blog
- SEO

## Phase 4

- API
- Search
- Queue

## Phase 5

- Visual Builder
- Plugin System

## Phase 6

- Multi Tenant
- Marketplace

---

# 🤝 Contributing

Contributions, issues, and feature requests are welcome.

Please read the [contribution guidelines](CONTRIBUTING.md) before submitting a pull request.

---

# 📄 License

This project is licensed under the MIT License.

---

<div align="center">

Made with ❤️ using Laravel

</div>