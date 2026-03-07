# Deployment Guide - Hostinger

This repository is set up to automatically deploy to Hostinger whenever you push to the `main` branch.

## Setup Instructions

### Step 1: Get Your Hostinger FTP Credentials

1. Log in to your Hostinger account: https://hpanel.hostinger.com
2. Go to **Hosting** → Select your hosting plan
3. Navigate to **Files** → **FTP Accounts**
4. Note down or create FTP credentials:
   - **FTP Server** (usually: ftp.yourdomain.com or an IP address)
   - **FTP Username**
   - **FTP Password**

### Step 2: Add Secrets to GitHub

1. Go to your GitHub repository: https://github.com/MartinRobyn/obzrvePhotography
2. Click **Settings** → **Secrets and variables** → **Actions**
3. Click **New repository secret** and add these three secrets:

   - **Name:** `FTP_SERVER`  
     **Value:** Your FTP server address (e.g., ftp.yourdomain.com)
   
   - **Name:** `FTP_USERNAME`  
     **Value:** Your FTP username
   
   - **Name:** `FTP_PASSWORD`  
     **Value:** Your FTP password

### Step 3: Push and Deploy

Once you've added the secrets, any push to the `main` branch will automatically deploy your site to Hostinger!

You can monitor deployments at:
https://github.com/MartinRobyn/obzrvePhotography/actions

## Manual Deployment

If you prefer to deploy manually, you can use an FTP client like FileZilla:
1. Download FileZilla: https://filezilla-project.org
2. Connect using your FTP credentials
3. Upload files from `Photography site/` folder to `/public_html/` on your server

## Notes

- The deployment uploads files to `/public_html/` - adjust the path in `.github/workflows/deploy.yml` if your hosting uses a different directory
- Initial deployment may take a few minutes
- Check GitHub Actions tab if deployment fails
