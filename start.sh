#!/bin/bash
echo "Starting VENTO Inventory Management System..."
echo ""
echo "Server will start at http://localhost:8000"
echo "Press Ctrl+C to stop the server"
echo ""
cd "$(dirname "$0")"
php -S localhost:8000

