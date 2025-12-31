# Pic-O - A Lightweight Photo Sharing Platform

**Pic-O** is a simple, elegant, and self-hosted photo publishing platform built with **Laravel 12** and **Bootstrap 5.x**.  
It’s designed for photographers, families, and self-hosters who want an easy way to organize and share their photo collections without the complexity or overhead of full-fledged photo management suites.

Unlike heavy solutions that try to automate everything, **Pic-O** keeps things simple:  
you create albums, fill them with your favorite photos, and publish them beautifully.

---

## Why Pic-O?

Modern gallery tools often aim to do too much: commenting, device syncing, editing, and even AI retouching.  
**Pic-O** focuses on doing one thing exceptionally well: *Make your photo archives effortlessly viewable and shareable.*

It’s built for simplicity, privacy, and performance, and designed to look great on any screen.

- 🪶 **Lightweight** -- built on Laravel: fast to deploy, simple to maintain  
- 🖼️ **Organized your way** -- easily create and managed dynamic albums  
- 🔗 **Share securely** -- login-based access and private, tokenized share links  
- 💫 **Beautiful viewing** -- animated masonry grid with slideshow and photo details on demand  
- 📱 **Mobile friendly** -- smooth navigation and management from any device  
- 🐳 **Easy to host** -- ships with a ready-to-use Docker container for quick setup  

---

## Philosophy

Pic-O is built on a few core principles:

- 🖼️ **Your photos are yours** -- we just help you show them off
- 🛠️ **Open source and community-driven** -- licensed under MIT for everyone to use and improve
- ⚡ **Performance, usability, and privacy** -- should be defaults, not options

If you value **clarity over clutter**, **ownership over cloud dependency**, and **beautiful simplicity**,  
**Pic-O** might be exactly what you’ve been looking for.

---

## Setup

Pic-O can be up and running in just a few minutes. You have two simple options depending on your environment and preferences.

### Option 1: Run with Docker Compose (Recommended)

The repository includes ready-to-use `docker-compose` files that build both the **application** and the **database** containers in one step.

#### 1. Clone the Repository

```bash
git clone https://github.com/Xirt/Pic-O.git
cd Pic-O
```

This will:
- Clone the Pic-O repository to a new directory named "Pic-O"
- Navigate to the new directory containing the Docker Compose files

> **Note:** You can modify ports, volumes, or environment variables in the docker-compose.yml and .ENV file in the "Pic-O"-directory to fit your environment.

#### 2. Launch the stack

```bash
docker compose up -d
```
This will:
- Build and start the MariaDB database container
- Build and start the Pic-O app container
- Expose the app on http://localhost:8080 by default

#### 3. Verify installation

Once the containers are running, verify everything is working by opening your browser and visiting:

```bash
http://localhost:8080
```

You should see the Pic-O setup screen allowing you to create an initial admin account. Once created, you will be redirected to the login screen.

### Option 2: Use the prebuilt Docker image
If you prefer a faster, ready-made deployment, you can use the published image on Docker Hub.

#### 1. Create new database

Create a database for Pic-O in MySQL, MariaDB, PostgreSQL or alike.

#### 2. Run the Pic-O container

Modify below command to connect to the created database and ensure the photo and storage paths exist. Afterwards, run the command from the terminal. 

```bash
docker run -d \
  --name pico_app \
  -p 8080:80 \
  -v /path/to/your/photos:/var/www/resources/photos:ro \
  -v /path/to/your/app-storage:/var/www/storage/app:rw \
  -e DB_CONNECTION=mysql \       # e.g., mysql, postgresql or mariadb
  -e DB_HOST=<db_host> \         # hostname or IP of your database
  -e DB_PORT=<db_port> \         # usually 3306 for MySQL/MariaDB, 5432 for PostgreSQL
  -e DB_DATABASE=<db_name> \     # your database name
  -e DB_USERNAME=<db_user> \     # database username
  -e DB_PASSWORD=<db_pass> \     # database password
  -e APP_HOST=<host_or_domain> \ # URL of your Pic-O instance
  xirtnl/pic-o:beta
```

This pulls the latest version of Pic-O and mounts your local photo directory inside the container.

#### 3. Verify installation

Once the containers are running, verify everything is working by opening your browser and visiting:

```bash
http://localhost:8080
```

You should see the Pic-O setup screen allowing you to create an initial admin account. Once created, you will be redirected to the login screen.
