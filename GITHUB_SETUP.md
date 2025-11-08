# GitHub Setup Instructions

## Quick Setup Commands

After creating a repository on GitHub, run these commands:

```bash
# Add the remote repository (replace YOUR_USERNAME and YOUR_REPO_NAME)
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git

# Rename branch to main (if needed)
git branch -M main

# Push to GitHub
git push -u origin main
```

## Step-by-Step Guide

### 1. Create Repository on GitHub
1. Go to https://github.com/new
2. Enter repository name (e.g., "StoreInventorySystem")
3. Choose Public or Private
4. **DO NOT** check "Initialize with README"
5. Click "Create repository"

### 2. Connect and Push
Copy the commands GitHub shows you, or use:

```bash
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git
git branch -M main
git push -u origin main
```

**Note:** Replace `YOUR_USERNAME` and `YOUR_REPO_NAME` with your actual GitHub username and repository name.

### 3. Authentication
- If prompted, use a Personal Access Token (not password)
- Create one at: https://github.com/settings/tokens
- Select scope: `repo` (full control of private repositories)

## Future Updates

After making changes to your code:

```bash
git add .
git commit -m "Description of changes"
git push
```

