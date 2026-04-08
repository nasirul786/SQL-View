# SQL View

A lightweight, purely PHP-based web application to safely view, explore, and export your structural databases. Built without bloated frameworks, utilizing a modern minimalist Google-inspired analytical UI.

## Features

- **Aesthetic Workspace**: Clean, responsive, and distraction-free UI utilizing Bootstrap 5 utility classes paired with exact Google Material typography.
- **Easy Secure Connections**: No tedious internal config files necessary. Just log into any reachable databases safely across your server.
- **Data Exploration**: 
  - Dynamic column parsing and seamless data pagination.
  - Filter string searching across all columns in a table.
  - Pop-up modal overlays for reading extensive cell contents effortlessly without destroying tabular alignment.
- **Google Sheets / Excel Exports**: Thanks to `simplexlsxgen`, quickly export individual sorted tables or export the **Entire Database** into a Google-Sheets ready `.xlsx` file (beautifully prefixed with custom background and text stylization).

## Setup instructions

1. **Clone the repository**:
   ```bash
   git clone https://github.com/nasirul786/SQL-View.git
   cd SQL-View
   ```
2. **Install Dependencies** (For XLSX generation):
   ```bash
   composer install
   ```
3. **Deploy & Run**:
   You can either upload this to any standard PHP host (cPanel, XAMPP, VPS) or test using PHP internal engine:
   ```bash
   php -S localhost:8000
   ```
   And visit `http://localhost:8000`.

## License
MIT
