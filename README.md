<h1 align="center">👤 NoahFace Event Sync</h1>

<p align="center">
  A Laravel middleware application for receiving and processing real-time event notifications from NoahFace access points.
</p>

<p align="center">
  <img alt="Laravel" src="https://img.shields.io/badge/Laravel-Framework-FF2D20?logo=laravel&logoColor=white">
  <img alt="PHP" src="https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php&logoColor=white">
  <img alt="Ngrok" src="https://img.shields.io/badge/Ngrok-Tunnel-1F1E25?logo=ngrok&logoColor=white">
  <img alt="Status" src="https://img.shields.io/badge/Status-Active-22C55E">
  <img alt="License" src="https://img.shields.io/badge/License-MIT-64748B">
</p>

---

## ✨ Highlights
- 📡 **Real-time Webhook:** Listens for clock-ins, access grants, and user events.
- 🔐 **Secure:** Implements Basic Authentication for NoahFace verification.
- 🚇 **Dev Friendly:** tailored instructions for **Ngrok** local development.
- ⚡ **Fast Response:** Optimized to return `200 OK` < 10s to prevent retry loops.
- 📝 **Log & Store:** Ready-to-use controller structure for logging or database persistence.

---

## compass Table of Contents
- [Overview](#-overview)
- [Tech Stack](#-tech-stack)
- [Requirements](#-requirements)
- [Getting Started](#-getting-started)
- [Local Development (Ngrok)](#-local-development-ngrok)
- [NoahFace Configuration](#-noahface-configuration)
- [Event Workflow](#-event-workflow)
- [Troubleshooting](#-troubleshooting)
- [License](#-license)

---

## ℹ️ Overview
[cite_start]NoahFace devices (time‑clocks, access control screens, or mobile apps) forward user interactions to your system via **Custom Notifications**. [cite: 3]

This application acts as the endpoint for those notifications. It handles:
1.  **Authentication:** Validating the request comes from NoahFace.
2.  **Ingestion:** accepting the JSON payload (User ID, Temperature, Event Type, etc.).
3.  [cite_start]**Response:** sending a strict `200 OK` to confirm receipt. [cite: 8]

---

## 🧰 Tech Stack
- **Framework:** Laravel 10/11
- **Language:** PHP 8.1+
- **Tunneling:** Ngrok (for local testing)
- **Database:** MySQL/PostgreSQL (Optional for storage)

---

## 📦 Requirements
- PHP ≥ 8.1
- Composer
- Ngrok CLI (for exposing local server)
- [cite_start]NoahFace Dashboard Access (Admin permissions) [cite: 16]

---

## 🚀 Getting Started

### 1) Clone the repository
```bash
git clone <your-repo-url>
cd noahface-sync

2) Install dependencies
Bash

composer install --prefer-dist --no-interaction

3) Setup environment
Bash

cp .env.example .env
php artisan key:generate

Update your .env with the credentials you want NoahFace to use:
Code snippet

APP_URL=http://localhost:8000

# NoahFace Authentication Credentials
NOAHFACE_USERNAME=developer
NOAHFACE_PASSWORD=yourStrongPassword

4) Migrate database (Optional)

If you are storing events in a database table:
Bash

php artisan migrate

🚇 Local Development (Ngrok)

Since NoahFace is a cloud service, it cannot reach localhost. You must use Ngrok to tunnel traffic.

1. Start Laravel
Bash

php artisan serve --port=8000

2. Start Ngrok

In a separate terminal:
Bash

ngrok http 8000

Copy the forwarding URL (e.g., https://1498df2569d2.ngrok-free.app).

3. Update Environment

Update APP_URL in your .env to match the dynamic Ngrok URL if your app generates absolute links.

🛠️ NoahFace Configuration

To connect your device to this app, configure a Notification in the NoahFace Dashboard.

Navigate to: Access Points → Notifications → + Add Notification
Setting	Value	Notes
Type	

Custom
	
Method	

POST
	
URL	https://[ngrok-id].ngrok-free.app/api/noahface/event	

Append /api/noahface/event to your Ngrok domain.

Security	

Basic
	
Username	developer	Must match NOAHFACE_USERNAME in .env
Password	******	Must match NOAHFACE_PASSWORD in .env

    ⚠️ Important: If using Ngrok Free Tier, your URL changes every restart. You must update the Notification URL in NoahFace every time you restart Ngrok. 

🗂️ Event Workflow

The following diagram illustrates how an event travels from the iPad/Device to your Laravel Controller.
Code snippet

sequenceDiagram
    participant D as NoahFace Device
    participant N as NoahFace Cloud
    participant G as Ngrok Tunnel
    participant L as Laravel App

    Note over D, N: User Clocks In
    D->>N: Sync Event Data
    N->>G: POST /api/noahface/event (Basic Auth)
    G->>L: Forward Request
    L->>L: Authenticate & Validate
    L->>L: Dispatch Job / Save to DB
    L-->>N: 200 OK (Must be <10s)

Example Payload

Your controller receives a JSON payload similar to this:

JSON

{
  "eventid": "109997645",
  "utc": "2026-01-23 21:03:47",
  "type": "clockin",
  "userid": "12345",
  "firstname": "Samara",
  "lastname": "Smith",
  "temperature": 36.9
}

🧯 Troubleshooting
🔴 404 Not Found

    Cause: The URL in NoahFace does not match your current Ngrok session.

    Fix: Check your terminal for the current https://....ngrok-free.app URL and paste it into NoahFace -> Notifications. 

🟠 Authentication Failed

    Cause: Mismatch between .env credentials and NoahFace settings.

    Fix: Ensure NOAHFACE_USERNAME and NOAHFACE_PASSWORD exactly match what you typed in the NoahFace "Security" section. 

🟡 Retries / Delays

    Cause: App taking longer than 10s to respond.

    Fix: Ensure you return response()->json(['status' => 'ok']) immediately. Dispatch heavy processing (emailing, payroll sync) to a Queue Job. 

📄 License

Private / Internal project. All trademarks are the property of their respective owners.
