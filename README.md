# 🌐 Web Platform with Advanced Security Features and User Interaction

## 📌 Project Overview

This application is a **web-based social platform**, designed to facilitate communication between users through an **integrated messaging module**. It offers:

-   **Secure user authentication.**
-   **Real-time interaction.**
-   **An optimized environment for seamless conversations.**

The platform places a strong emphasis on **advanced security**, leveraging robust technologies for data management and information protection.

## 🚀 Technologies Used

-   **PHP 8.4.8** – Backend processing and application logic.
-   **Apache Web Server 2.4.63** – Handling HTTP requests.
-   **MariaDB 11.7.2** – Database storage and management.
-   **PHPMyAdmin 5.2.2** – Database administration interface.

## 🔧 Installation & Configuration

### ⚙️ Prerequisites:

To run the application, ensure you have:

-   **Docker Engine** installed and configured.

### 🏗️ Installation Steps:

1. **Clone the repository**:

    ```bash
    git clone https://github.com/ieremiegabriela/licenta.git
    cd path_to/licenta
    ```

2. **Verify that Docker Engine is installed** on your system.

3. **Ensure the following ports are available**:

    - `3306:3306` – MariaDB
    - `8080:80` – PHPMyAdmin
    - `80:80` – HTTPD (Apache Web Server)

4. **Launch the application using Docker Compose**:

    ```bash
    docker compose up
    ```

5. **Access the project in your preferred browser** using:
    - [http://127.0.0.1:80](http://127.0.0.1:80)
    - [http://localhost:80](http://localhost:80)
