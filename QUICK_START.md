# 🚀 Quick Start Guide - Run on Localhost

## Step 1: Install Dependencies (Already Done!)
✅ Dependencies are already installed!

## Step 2: Create Environment File

The `.env.local` file should be created automatically. If not, create it manually in the root folder with this content:

```env
MONGODB_URI=mongodb+srv://Vento:Vento@vento.gknvzdv.mongodb.net/?appName=VENTO
MONGODB_DB=vento_inventory
JWT_SECRET=vento-secret-key-change-in-production-2024
NEXTAUTH_SECRET=vento-nextauth-secret-change-in-production-2024
NEXTAUTH_URL=http://localhost:3000
```

## Step 3: Run the Development Server

Open PowerShell or Command Prompt in this folder and run:

```bash
npm run dev
```

Or if you prefer:

```bash
npx next dev
```

## Step 4: Open in Browser

Once the server starts, you'll see:

```
- ready started server on 0.0.0.0:3000, url: http://localhost:3000
```

Open your browser and go to:
**http://localhost:3000**

## 🎉 You're Ready!

The application will:
- Auto-reload when you make changes
- Show compilation errors in the browser
- Run on http://localhost:3000

## 📝 Common Commands

- **Start development:** `npm run dev`
- **Build for production:** `npm run build`
- **Start production server:** `npm start`
- **Check for errors:** `npm run lint`

## ⚠️ Troubleshooting

### Port 3000 already in use?
Change the port by running:
```bash
npm run dev -- -p 3001
```

### Can't connect to MongoDB?
Check your `.env.local` file has the correct MongoDB connection string.

### Module not found errors?
Run `npm install` again.

