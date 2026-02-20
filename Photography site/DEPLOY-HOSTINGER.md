# Connect GitHub to Hostinger (Photography site)

Follow these steps to connect this repo to Hostinger and deploy (with optional auto-deploy on every push).

---

## Part 1 – Push this site to GitHub

If the project is not in Git yet:

1. **Initialize Git and push to GitHub**
   - Open a terminal in the `Photography site` folder.
   - Run:
     ```bash
     git init
     git add .
     git commit -m "Initial commit - photography site"
     ```
   - On [GitHub](https://github.com/new), create a **new repository** (e.g. `photography-site`). Do **not** add a README, .gitignore, or license.
   - Add the remote and push (replace `YOUR_USERNAME` and `YOUR_REPO` with your GitHub username and repo name):
     ```bash
     git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO.git
     git branch -M main
     git push -u origin main
     ```

---

## Part 2 – Connect GitHub to Hostinger (hPanel)

**If you only see “Websites list” and “Migrations”:**  
That’s the main left menu. You need to open a **specific website** first:

1. Click **Websites** (or **Websites list**).
2. You should see your website(s) listed (each with a domain name and the plan).
3. Next to the site you want, click **Dashboard** (Hostinger often uses this instead of “Manage”).  
   If you see **Manage** instead, click that. Same result.
4. That opens the **hosting dashboard** with a **new left sidebar** (File Manager, Domain, Email, etc.).
5. In that sidebar, use the **search box** and type **Git**, then open **Git**.  
   If there’s no search, look under **Advanced** for **Git**.

**No website in the list?** You may need to **add a website** first (e.g. “Add website” / connect a domain) so a hosting plan and dashboard exist.

**Git not there?** Git is only on **hosting** plans (Web/Cloud), not on “Website Builder”‑only. Use **Alternative: Deploy via FTP** at the end of this file.

---

1. **Log in to Hostinger**  
   Go to [hPanel](https://hpanel.hostinger.com) and sign in.

2. **Open Git**
   - Go to **Websites** → click **Dashboard** (or **Manage**) next to the site you want.
   - On the hosting dashboard, in the left sidebar, search for **Git** (or find it under **Advanced**) and click it.

3. **Generate an SSH key (for private repos or recommended for public)**
   - Click **Generate Key**.
   - Copy the generated **public key** (you’ll add it to GitHub in the next step).

4. **Add the SSH key to GitHub**
   - GitHub → your **profile photo** → **Settings**.
   - Left sidebar: **SSH and GPG keys** → **New SSH key**.
   - **Title:** e.g. `Hostinger - photography site`.
   - **Key:** paste the key from Hostinger.
   - Click **Add SSH key** (confirm with your GitHub password if asked).

5. **Create the Git repository in Hostinger**
   - Back in Hostinger → **Git** → **Create a New Repository**.
   - **Repository address:**
     - **Private repo:** `git@github.com:YOUR_USERNAME/YOUR_REPO.git`
     - **Public repo:** you can use the same SSH URL or `https://github.com/YOUR_USERNAME/YOUR_REPO.git`
   - **Branch:** `main` (or the branch you use).
   - **Install path:** leave **empty** so the site deploys to the root (`public_html`).
   - Click to create the repository.

6. **First deploy**
   - Click **Deploy** to run the first deployment.
   - Your site should be live at your domain after the deploy finishes.

---

## Part 3 – Auto-deploy on every push (optional)

1. In Hostinger **Git**, select your repository.
2. Click **Auto-Deployment** (or **Continuous Deployment**).
3. Copy the **Webhook URL** Hostinger shows.
4. On GitHub:
   - Open your repo → **Settings** → **Webhooks** → **Add webhook**.
   - **Payload URL:** paste the Hostinger webhook URL.
   - **Content type:** `application/json`.
   - **Which events:** choose **Just the push event** (or “Let me select…” and enable **Pushes**).
   - Click **Add webhook**.

After this, every `git push` to the connected branch will trigger a new deployment on Hostinger.

---

## Notes

- **Empty `public_html`:** If you had files in `public_html` before, the first Git deploy may require that folder to be empty. Back up any existing files, then deploy.
- **Private repo:** Use the SSH URL (`git@github.com:...`) and the Hostinger SSH key in GitHub as in Part 2.
- **Branch:** Use the same branch name in Hostinger as the one you push (usually `main`).

If something fails, check **Latest Build** in Hostinger’s Git section for the deployment log.

---

## 403 Forbidden after deploy

If your domain shows **403 Forbidden** after a Git deploy:

1. **Check where the files are**  
   In hPanel open **File Manager** and go to **public_html**.
   - If you see **index.html** directly in `public_html` → the path is correct; check permissions (step 2) and .htaccess (step 3).
   - If you see a **folder** (e.g. `obzrvePhotography`) and your files are inside it → the web root is empty. Either:
     - **Option A:** In Hostinger **Git**, delete the repo and create it again with **Install path** set to `public_html` (if your panel allows that), or  
     - **Option B:** In File Manager, move everything from `public_html/obzrvePhotography/` up into `public_html` (select all files/folders → Move → `public_html`), then delete the now-empty repo folder.

2. **Permissions**  
   - Folder **public_html**: right‑click → Permissions → **755**.  
   - **index.html** (and other files): **644**.

3. **.htaccess**  
   If there is a **.htaccess** in `public_html`, rename it to `.htaccess.bak` and reload the site. If 403 goes away, the rules in .htaccess were blocking access; fix or remove the bad rules.

---

## Alternative: Deploy via FTP (if Git is not available)

If your Hostinger plan doesn’t show **Git** (e.g. Website Builder or a different product), you can still deploy from GitHub using **FTP**:

1. In hPanel, go to **Websites** → **Manage** your site → **File Manager** (or **FTP**). Note your FTP host, username, and password (or set an FTP account).
2. Push your photography site to GitHub (Part 1 above).
3. Use a **GitHub Action** to deploy on push: in your repo add `.github/workflows/deploy-ftp.yml` that uses your FTP credentials (stored as GitHub secrets). You can use the “FTP Deploy” or “Deploy to Hostinger” actions from the GitHub Marketplace and add secrets: `FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD`, and optionally `FTP_REMOTE_DIR` (e.g. `public_html`).

If you want, I can provide a ready-to-use `deploy-ftp.yml` for this project.
