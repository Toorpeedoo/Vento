# Vercel Deployment Fix Guide

## The Problem
You're getting an "Internal Server Error" on Vercel because the environment variables are not configured.

## Solution: Add Environment Variables to Vercel

### Step 1: Go to Vercel Dashboard
1. Visit: https://vercel.com/dashboard
2. Select your project (VENTO)
3. Go to **Settings** → **Environment Variables**

### Step 2: Add These Environment Variables

Add each of these variables with the values below:

| Variable Name | Value |
|--------------|-------|
| `MONGODB_URI` | `mongodb+srv://Vento:Vento@vento.gknvzdv.mongodb.net/?appName=VENTO` |
| `MONGODB_DB` | `vento_inventory` |
| `JWT_SECRET` | `vento-secret-key-change-in-production-2024` |
| `NEXTAUTH_SECRET` | `vento-nextauth-secret-change-in-production-2024` |
| `NEXTAUTH_URL` | `https://your-project-name.vercel.app` (replace with your actual Vercel URL) |

**Important:** For `NEXTAUTH_URL`, use your actual Vercel deployment URL like:
- `https://vento.vercel.app` OR
- `https://your-custom-domain.com`

### Step 3: Redeploy

After adding the environment variables:
1. Go to **Deployments** tab
2. Click the **three dots** on the latest deployment
3. Click **Redeploy**

OR push a new commit:
```bash
git add .
git commit -m "Fix environment variables"
git push
```

## Alternative: Use Vercel CLI

```bash
# Install Vercel CLI
npm i -g vercel

# Login
vercel login

# Add environment variables
vercel env add MONGODB_URI
# Paste: mongodb+srv://Vento:Vento@vento.gknvzdv.mongodb.net/?appName=VENTO

vercel env add MONGODB_DB
# Type: vento_inventory

vercel env add NEXTAUTH_SECRET
# Type: vento-nextauth-secret-change-in-production-2024

vercel env add NEXTAUTH_URL
# Type: https://your-project.vercel.app

# Redeploy
vercel --prod
```

## Additional Notes

### Update NEXTAUTH_URL After First Deploy
1. After your first successful deployment, Vercel will give you a URL
2. Update the `NEXTAUTH_URL` variable with that URL
3. Redeploy again

### Security Recommendations
- Change the `JWT_SECRET` and `NEXTAUTH_SECRET` to random strings in production
- Generate random secrets using: `openssl rand -base64 32`

### Common Issues

**Still getting errors?**
Check the Vercel logs:
1. Go to your project dashboard
2. Click on the failed deployment
3. Check the **Function Logs** or **Build Logs** for specific errors

**MongoDB Connection Issues?**
- Ensure your MongoDB Atlas IP whitelist includes `0.0.0.0/0` (all IPs)
- Or add Vercel's IP ranges to MongoDB Atlas whitelist

## Test After Deployment

Visit your Vercel URL:
- https://your-project.vercel.app
- https://your-project.vercel.app/login
- https://your-project.vercel.app/api/test (if you have a test endpoint)
