# MongoDB Atlas Configuration for Vercel

## Allow Vercel to Connect to MongoDB Atlas

### Step 1: Whitelist Vercel IPs in MongoDB Atlas

1. Go to: https://cloud.mongodb.com/
2. Select your cluster
3. Click **Network Access** (left sidebar)
4. Click **Add IP Address**
5. Choose **ALLOW ACCESS FROM ANYWHERE**
   - This adds `0.0.0.0/0` to the whitelist
   - Required for Vercel serverless functions
6. Click **Confirm**

**Security Note:** While `0.0.0.0/0` allows all IPs, your database is still protected by username/password authentication.

### Alternative: Add Specific Vercel IPs (More Secure)

If you want more security, add these Vercel IP ranges:
```
76.76.21.0/24
76.76.21.21
76.76.21.142
76.76.21.164
```

However, Vercel uses many IPs for serverless functions, so `0.0.0.0/0` is often necessary.

## Check Your Current MongoDB Settings

### 1. Network Access
- ✓ Should show: `0.0.0.0/0` (Allows access from anywhere)

### 2. Database Access
- Username: `Vento`
- Password: `Vento`
- ✓ Should have "Read and write to any database" permissions

### 3. Connection String
Your current connection string:
```
mongodb+srv://Vento:Vento@vento.gknvzdv.mongodb.net/?appName=VENTO
```

This is correct! ✓

## Test Connection from Vercel

After deploying, you can test if Vercel can connect to MongoDB:

1. Create an API test endpoint (already exists if you have `/api/test`)
2. Visit: `https://your-project.vercel.app/api/test`
3. It should return a success message if MongoDB is accessible

## Common MongoDB + Vercel Issues

### Issue: "MongoServerError: bad auth"
**Solution:** 
- Check username/password in connection string
- Ensure Database Access user exists in MongoDB Atlas
- Password special characters must be URL-encoded

### Issue: "MongoServerSelectionError: connection timed out"
**Solution:**
- Add `0.0.0.0/0` to MongoDB Atlas Network Access
- Check if MongoDB cluster is active (not paused)
- Increase `serverSelectionTimeoutMS` in connection options

### Issue: "ENOTFOUND vento.gknvzdv.mongodb.net"
**Solution:**
- DNS issue - usually temporary
- Check MongoDB cluster status
- Verify connection string is correct

## Current Status Check

Run this checklist:

- [ ] MongoDB Atlas Network Access allows `0.0.0.0/0`
- [ ] Database user `Vento` exists with correct password
- [ ] Environment variables added to Vercel
- [ ] `NEXTAUTH_URL` matches your Vercel deployment URL
- [ ] Project redeployed after adding env variables
- [ ] MongoDB cluster is active (not paused)
