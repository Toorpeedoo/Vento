# VENTO - Modern Inventory Management System

A modern, full-stack inventory management system built with Next.js 14, TypeScript, MongoDB, and Tailwind CSS.

## ✨ Features

- 🔐 **Authentication System** - Secure login and signup with session management
- 📦 **Product Management** - Add, view, update, and delete products
- 📊 **Real-time Inventory** - Track quantities and prices
- 👥 **User Management** - Admin dashboard for managing users
- 🎨 **Modern UI** - Beautiful, responsive design with Tailwind CSS
- ⚡ **Performance** - Built with Next.js 14 App Router for optimal performance
- 🚀 **Vercel Ready** - Deploy to Vercel with zero configuration

## 🚀 Getting Started

### Prerequisites

- Node.js 18+ 
- MongoDB Atlas account (or local MongoDB)
- npm or yarn

### Installation

1. **Install dependencies:**
```bash
npm install
```

2. **Set up environment variables:**
```bash
cp .env.local.example .env.local
```

Edit `.env.local` and add your configuration:
```
MONGODB_URI=your-mongodb-connection-string
MONGODB_DB=your-database-name
JWT_SECRET=your-super-secret-jwt-key-change-this-in-production
NEXTAUTH_SECRET=your-nextauth-secret-change-this-in-production
NEXTAUTH_URL=http://localhost:3000
```

3. **Run the development server:**
```bash
npm run dev
```

4. **Open your browser:**
Navigate to [http://localhost:3000](http://localhost:3000)

## 📁 Project Structure

```
vento-inventory/
├── app/                    # Next.js App Router pages
│   ├── api/               # API routes
│   │   ├── auth/         # Authentication endpoints
│   │   ├── products/     # Product CRUD operations
│   │   └── admin/        # Admin endpoints
│   ├── login/            # Login page
│   ├── signup/           # Signup page
│   ├── dashboard/        # User dashboard
│   └── admin/            # Admin dashboard
├── components/            # React components
├── lib/                   # Utility functions
│   ├── db/              # Database utilities
│   ├── auth.ts          # Authentication helpers
│   └── mongodb.ts       # MongoDB connection
└── public/               # Static assets
```

## 🎨 Design

The application features a modern, clean design with:
- Gradient backgrounds and buttons
- Smooth animations and transitions
- Responsive layout for all devices
- Intuitive user interface
- Professional color scheme

## 🔧 Tech Stack

- **Framework:** Next.js 14 (App Router)
- **Language:** TypeScript
- **Database:** MongoDB
- **Styling:** Tailwind CSS
- **Authentication:** JWT with HTTP-only cookies
- **Icons:** Lucide React

## 📦 API Endpoints

### Authentication
- `POST /api/auth/login` - User login
- `POST /api/auth/signup` - User registration
- `POST /api/auth/logout` - User logout
- `GET /api/auth/session` - Get current session

### Products
- `GET /api/products` - Get all products
- `POST /api/products` - Create product
- `GET /api/products/[id]` - Get product by ID
- `PUT /api/products/[id]` - Update product
- `DELETE /api/products/[id]` - Delete product
- `POST /api/products/[id]/quantity` - Update quantity

### Admin
- `GET /api/admin/users` - Get all users
- `DELETE /api/admin/users` - Delete user

## 🚀 Deployment

### Deploy to Vercel

1. Push your code to GitHub
2. Import your repository to Vercel
3. Add environment variables in Vercel dashboard
4. Deploy!

The application is optimized for Vercel deployment.

## 📝 License

This project is open source and available under the MIT License.

## 🤝 Contributing

Contributions, issues, and feature requests are welcome!

## 📧 Support

For support, please open an issue in the GitHub repository.
