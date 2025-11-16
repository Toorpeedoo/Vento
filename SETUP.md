# 🚀 VENTO - Next.js Setup Guide

## Quick Start

### 1. Install Dependencies
```bash
npm install
```

### 2. Create Environment File
Create `.env.local` in the root directory:
```env
MONGODB_URI=your-mongodb-connection-string
MONGODB_DB=your-database-name
JWT_SECRET=your-super-secret-jwt-key-change-this-in-production
NEXTAUTH_SECRET=your-nextauth-secret-change-this-in-production
NEXTAUTH_URL=http://localhost:3000
```

**Important:** 
- Never commit `.env.local` to version control!
- Use strong, random strings for `JWT_SECRET` and `NEXTAUTH_SECRET` in production
- Keep your MongoDB credentials secure

### 3. Run Development Server
```bash
npm run dev
```

Open [http://localhost:3000](http://localhost:3000) in your browser.

## 📁 Project Structure

```
vento-inventory/
├── app/                    # Next.js App Router
│   ├── api/               # API routes
│   │   ├── auth/         # Authentication
│   │   ├── products/     # Product operations
│   │   └── admin/        # Admin operations
│   ├── login/            # Login page
│   ├── signup/           # Signup page
│   ├── dashboard/        # User dashboard
│   ├── admin/            # Admin dashboard
│   └── layout.tsx        # Root layout
├── components/            # React components
│   ├── Navbar.tsx        # Navigation component
│   └── AuthGuard.tsx     # Protected route wrapper
├── lib/                   # Utilities
│   ├── db/               # Database utilities
│   ├── auth.ts           # Authentication helpers
│   ├── mongodb.ts        # MongoDB connection
│   └── types.ts          # TypeScript types
├── public/                # Static assets
└── package.json          # Dependencies
```

## 🎨 Design Features

- ✨ **Modern UI/UX** - Gradient backgrounds, smooth animations
- 📱 **Responsive** - Works on all devices
- 🎯 **User-Friendly** - Intuitive navigation and forms
- 🚀 **Fast** - Next.js optimization
- 🔒 **Secure** - JWT authentication with HTTP-only cookies

## 🌐 Deploy to Vercel

1. **Push to GitHub:**
   ```bash
   git add .
   git commit -m "Converted to Next.js"
   git push
   ```

2. **Import to Vercel:**
   - Go to [vercel.com](https://vercel.com)
   - Click "Import Project"
   - Select your repository

3. **Add Environment Variables:**
   - Add all variables from `.env.local`
   - Make sure to change `JWT_SECRET` and `NEXTAUTH_SECRET` for production!

4. **Deploy:**
   - Click "Deploy"
   - Your app will be live in minutes!

## 🔐 Default Features

- **Authentication:** JWT with HTTP-only cookies
- **Passwords:** Stored as plain text (for admin viewing as requested)
- **Database:** MongoDB Atlas
- **Styling:** Tailwind CSS

## 📝 Notes

- All PHP files have been removed
- MongoDB connection requires your own credentials
- Data structure remains compatible
- All existing data will work with the new system
- **Never commit `.env.local` to version control!**

## 🎉 You're All Set!

Your modern Next.js application is ready to use! Enjoy the beautiful new interface and improved performance.

