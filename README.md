# SQL View

A lightweight, purely PHP-based web application to safely view, explore, and export your SQL databases. Built with a modern, minimalist Google-inspired UI for speed and simplicity.

## ✨ New Features

- **Smart Command Parsing**: Paste a standard MySQL command string (e.g., `mysql -h host -u user -p`) into any login field, and the app will automatically parse and fill the form for you.
- **Optional Database Login**: Connect to your server without specifying a database. You'll be presented with a clean, searchable grid of all available databases to choose from.
- **Direct Link Login**: Log in and jump straight to a specific table or search result using URL parameters. Sensitive credentials are automatically stripped from the URL after the session is established for security.
- **More Detail View**: Cells with long text (60+ characters) are automatically truncated with a blue "...more" link. Click it to view the full content in a beautiful modal overlay.

## 🚀 Core Functionality

- **Google Workspace Aesthetic**: Clean, responsive, and distraction-free UI utilizing Roboto typography and Material Design principles.
- **Data Exploration**: 
  - Dynamic column parsing and seamless data pagination.
  - Global search across all columns in any table.
- **Professional Exports**: Export individual tables or the **Entire Database** into a Google-Sheets ready `.xlsx` file.
  - *Custom Styling*: Exports include bold, centered headers with a black background and yellow text for maximum readability.
  - *Smart Naming*: Sheets are automatically named after your database or table.

## 🛠️ Setup Instructions

1. **Clone the repository**:
   ```bash
   git clone https://github.com/nasirul786/SQL-View.git
   cd SQL-View
   ```
2. **Install Dependencies**:
   ```bash
   composer install
   ```
3. **Deploy & Run**:
   Run using your preferred PHP environment (XAMPP, VPS, etc.) or the built-in server:
   ```bash
   php -S localhost:8000
   ```

## 🔗 Direct URL Login Syntax
Jump directly to a view using this format:
`https://your-domain.com/view.php?host=HOST&user=USER&pass=PASS&db=DB&table=TABLE&search=QUERY`

## 📜 License
MIT
