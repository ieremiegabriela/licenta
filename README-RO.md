# 🌐 Platformă Web cu Elemente de Securitate Avansată și Interacțiune între Utilizatori

## 📌 Descrierea Proiectului

Această aplicație este o **platformă socială web**, concepută pentru a facilita comunicarea între utilizatori printr-un **modul de mesagerie integrat**. Oferă:

-   Autentificare securizată a utilizatorilor.
-   Interacțiune în timp real.
-   Un mediu optimizat pentru conversații fluide.

Platforma pune accent pe securitate avansată, utilizând tehnologii robuste pentru gestionarea datelor și protecția informațiilor.

## 🚀 Tehnologii Utilizate

-   **PHP 8.4.8** – Procesare backend și logica aplicației.
-   **Apache Web Server 2.4.63** – Gestionarea cererilor HTTP.
-   **MariaDB 11.7.2** – Stocarea și administrarea bazelor de date.
-   **PHPMyAdmin 5.2.2** – Interfață pentru gestionarea bazei de date.

## 🔧 Instalare și Configurare

### ⚙️ Cerințe preliminare:

Pentru a rula aplicația, aveți nevoie de:

-   **Docker Engine** instalat și configurat.

### 🏗️ Pași pentru instalare:

1. **Clonați repository-ul**:

    ```bash
    git clone https://github.com/ieremiegabriela/licenta.git
    cd path_to/licenta
    ```

2. **Verificați existența Docker Engine** pe sistemul dvs.

3. **Asigurați-vă că următoarele porturi sunt disponibile**:

    - `3306:3306` – MariaDB
    - `8080:80` – PHPMyAdmin
    - `80:80` – HTTPD (Apache WEB Server)

4. **Lansați aplicația utilizând Docker Compose**:

    ```bash
    docker compose up
    ```

5. **Accesați proiectul din browser** folosind:
    - [http://127.0.0.1:80](http://127.0.0.1:80)
    - [http://localhost:80](http://localhost:80)
