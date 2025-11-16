# 🔧 Vercel Troubleshooting Guide

## Internal Server Error on Login/Signup

If you're getting "Internal Server Error" when trying to login or create an account, follow these steps:

### Step 1: Check Environment Variables in Vercel

1. Go to your Vercel project dashboard
2. Click **Settings** → **Environment Variables**
3. Make sure you have ALL of these variables set:

```
MONGODB_URI=mongodb+srv://Vento:Vento@vento.gknvzdv.mongodb.net/?appName=VENTO
MONGODB_DB=vento_inventory
JWT_SECRET=your-random-secret-string-here
NEXTAUTH_SECRET=your-random-secret-string-here
NEXTAUTH_URL=https://your-app.vercel.app
```

**Important:**
- Replace `JWT_SECRET` and `NEXTAUTH_SECRET` with random strings (not the example values!)
- Set `NEXTAUTH_URL` to your actual Vercel deployment URL
- Make sure all variables are set for **Production**, **Preview**, and **Development** environments

### Step 2: Check Vercel Logs

1. Go to your Vercel project
2. Click **Deployments** tab
3. Click on your latest deployment
4. Click **Functions** tab or **Logs** tab
5. Look for error messages

### Step 3: Test MongoDB Connection

Visit this URL on your deployed site:
```
https://your-app.vercel.app/api/test
```

This will show you:
- ✅ If MongoDB is connecting successfully
- ✅ What environment variables are set
- ✅ What collections exist
- ❌ Any connection errors

### Step 4: Common Issues

#### Issue: MongoDB Connection String Not Set
**Solution:** Add `MONGODB_URI` in Vercel environment variables

#### Issue: JWT_SECRET Not Set
**Solution:** Add a random secret string for `JWT_SECRET`

#### Issue: Cookies Not Working
**Solution:** Make sure you're using HTTPS (Vercel provides this automatically)

#### Issue: Database Name Wrong
**Solution:** Check `MONGODB_DB` is set to `vento_inventory`

### Step 5: Redeploy After Adding Variables

After adding environment variables:
1. Go to **Deployments** tab
2. Click **⋯** on your latest deployment
3. Click **Redeploy**

This ensures the new environment variables are used.

### Quick Check Commands

To verify your environment variables are set correctly, you can check the test endpoint:
```
GET /api/test
```

This will return information about your MongoDB connection and environment setup (without exposing sensitive data).

## Still Not Working?

Check the Vercel Function Logs for specific error messages. The improved error handling will now log detailed errors that can help identify the issue.

