NoahFace Event Sync (Laravel)

This Laravel project provides a simple webhook endpoint for receiving event notifications from NoahFace
. NoahFace devices (time‑clocks, access control screens or mobile apps) can forward user events to your own systems via a custom notification. This application listens for those events and can be extended to integrate with payroll, ERP or other internal services.

Overview

NoahFace exposes an Event Notification API
 which forwards details of each user interaction via HTTP. Events include identifiers (such as eventid, userid and number), timestamps, organisational and device information, event type, and optional detail fields

. Your webhook must return an HTTP 200 status quickly (ideally <10 s) – otherwise the event will be re‑sent using an exponential backoff schedule. NoahFace supports Basic, Bearer token and OAuth 2.0 authentication

; this project uses Basic Auth for simplicity.

Prerequisites

PHP ≥ 8.1 with required extensions for Laravel.

Composer for dependency management.

A database supported by Laravel (e.g. MySQL, PostgreSQL or SQLite) if you intend to persist data.

An ngrok
 account and the Ngrok CLI for exposing your local server to NoahFace.

Access to the NoahFace dashboard with permission to manage Access Points and Notifications.

Installation

Clone this repository and change into the project directory:

git clone <your‑repo‑url>
cd noahface-sync


Install PHP dependencies via Composer:

composer install --prefer-dist --no-interaction --no-suggest


Configure environment variables:

Copy .env.example to .env:

cp .env.example .env


Set your APP_NAME and APP_URL. When testing locally with ngrok, APP_URL should be the full ngrok URL (for example https://1498df2569d2.ngrok-free.app).

Set up database credentials (DB_* variables) if you intend to store events.

Define the Basic Auth credentials that NoahFace will use. For example:

NOAHFACE_USERNAME=developer
NOAHFACE_PASSWORD=yourStrongPassword


Generate an application key:

php artisan key:generate


Migrate the database (optional). If you have created database tables to persist events, run:

php artisan migrate

Running the application locally

Start the Laravel development server:

php artisan serve --host=0.0.0.0 --port=8000


The API endpoint for NoahFace will be available at http://localhost:8000/api/noahface/event. When ngrok forwards to your local machine, NoahFace will call the URL https://<ngrok-domain>/api/noahface/event using the POST method with a JSON payload

.

Exposing your local server with ngrok

Install the ngrok CLI and authenticate it (see the ngrok documentation
).

Launch ngrok, forwarding port 8000 to the internet. Run this in a separate terminal:

ngrok http 8000


ngrok will display a forwarding URL like https://1498df2569d2.ngrok-free.app 
screenshot
 that points to your local Laravel server. Note this URL.

Update APP_URL in your .env file to match the ngrok URL so that any URL generation (e.g. in notifications) is correct.

Configuring NoahFace

To send event notifications from NoahFace to your application:

Log in to the NoahFace dashboard and navigate to your organisation and site (e.g. “Inglewood Farms”). Choose your access point or create one.

Add a custom notification:

Go to Access Points → Notifications and click Add Notification.

Set Type to Custom and Method to POST.

In the Notification URL field, enter your ngrok URL followed by /api/noahface/event, e.g.:

https://1498df2569d2.ngrok-free.app/api/noahface/event.

Choose Security → Basic and enter the username (developer) and password defined in your .env file. This will cause NoahFace to send an Authorization: Basic … header on each request.

Save the notification. You should see it listed in the notifications table; if the URL is invalid you may see errors such as 404 Not Found
screenshot
. Ensure the route exists and the ngrok tunnel is running.

Associate the notification with your access point so that events generated on the device are forwarded to your application.

Test the integration by performing a clock‑in or access event on the device. You can view incoming requests in your ngrok console and check Laravel logs (storage/logs/laravel.log) for processed events.

Handling events

The application defines a route, typically in routes/api.php, to handle incoming notifications:

Route::post('/noahface/event', [\App\Http\Controllers\NoahFaceController::class, 'handleEvent']);


Your handleEvent method should:

Authenticate the request using the NOAHFACE_USERNAME and NOAHFACE_PASSWORD values.

Validate the payload. NoahFace sends a JSON body containing fields such as eventid, utc, time, org, site, device, type, userid, firstname, lastname, etc.

. See the Event Notification API documentation
 for the full schema.

Persist or process the data according to your business logic (e.g. store in a database, push to a queue or send to a downstream API). Because NoahFace expects a quick response, you should queue heavy work and immediately return a 200 OK response. If you return a non‑200 status, NoahFace will retry the notification using exponential backoff

.

The example JSON below shows a typical payload from NoahFace:

{
  "eventid": "109997645",
  "utc": "2018-12-21 21:03:47",
  "time": "2018-12-22 08:03:47",
  "org": "Acme Corporation",
  "site": "Sydney",
  "device": "Time Clock",
  "devid": "",
  "type": "clockin",
  "detail": "",
  "method": "face",
  "userid": "12345",
  "number": "1001",
  "firstname": "Samara",
  "lastname": "Smith",
  "usertype": "Engineer",
  "cardnum": "",
  "temperature": 36.9,
  "elevated": false
}

Notes on security and error handling

Basic Auth credentials are transmitted over HTTPS in the Authorization header. Keep these credentials secret and rotate them regularly.

If your application needs to serve multiple organisations, consider implementing token‑based or OAuth 2.0 authentication as described in NoahFace’s documentation

.

Always return 200 OK as quickly as possible after storing or queuing the event. Long‑running processing should be offloaded to a job queue. If your endpoint is unavailable or returns an error, NoahFace will retry over a period of up to 90 days

.

Troubleshooting

ngrok not forwarding: Ensure ngrok is running (ngrok http 8000) and that your firewall allows inbound connections.

404 Not Found errors: Verify that the route /api/noahface/event exists and that APP_URL matches the ngrok URL. The screenshot from the NoahFace dashboard shows 404 errors when the URL is incorrect
screenshot
.

Authentication errors: Double‑check the username and password configured in .env and in the NoahFace notification.

Contributing

Feel free to fork this repository and open pull requests. Please follow the PSR‑12 coding style and include tests for any new functionality.

License

This project is provided without any warranty. Consult your organisation’s legal team before deploying it in production. All trademarks are the property of their respective owners.
