# Push this project to GitHub

## 1. Install Git (if you haven’t)

1. Download **Git for Windows**: https://git-scm.com/download/win  
2. Run the installer (defaults are fine).  
3. **Restart Cursor** (or your terminal) so it picks up the new `git` command.

---

## 2. Create a new repo on GitHub

1. Go to **https://github.com** and sign in.  
2. Click the **+** (top right) → **New repository**.  
3. **Repository name:** e.g. `photography-site` or `photono-gallery`.  
4. Choose **Public**.  
5. **Do not** check “Add a README” or “Add .gitignore” (you already have files).  
6. Click **Create repository**.  
7. Leave the page open — you’ll need the repo URL (e.g. `https://github.com/YourUsername/photography-site.git`).

---

## 3. Run these commands in your project folder

Open **Terminal** in Cursor (**View → Terminal** or `` Ctrl+` ``), then run the commands below **one at a time**. Replace `YOUR_USERNAME` and `REPO_NAME` with your GitHub username and repo name.

```powershell
cd "C:\Users\Robyn\Documents\Photography site"
```

```powershell
git init
```

```powershell
git add .
```

```powershell
git commit -m "Initial commit: Photono site and client gallery"
```

```powershell
git branch -M main
```

```powershell
git remote add origin https://github.com/YOUR_USERNAME/REPO_NAME.git
```

```powershell
git push -u origin main
```

- When you run `git push`, a browser or prompt may ask you to **sign in to GitHub** (or use a **Personal Access Token** instead of a password).  
- If GitHub asks for a token: go to GitHub → **Settings** → **Developer settings** → **Personal access tokens** → create a token with `repo` scope, then paste it when prompted for a password.

---

## 4. One-line version (after you’ve run `git init` and `git add .` and `git commit` once)

If you’ve already run the first four commands and only need to connect and push:

```powershell
git remote add origin https://github.com/YOUR_USERNAME/REPO_NAME.git
git branch -M main
git push -u origin main
```

---

## Summary

| Step            | What to do |
|-----------------|------------|
| Install Git     | https://git-scm.com/download/win, then restart Cursor |
| Create repo     | GitHub → New repository (no README / .gitignore) |
| Init & commit   | `git init` → `git add .` → `git commit -m "Initial commit: ..."` |
| Connect & push  | `git remote add origin <URL>` → `git branch -M main` → `git push -u origin main` |

Your `.gitignore` already excludes `private/config.php` and `private/uploads/*`, so secrets and uploads won’t be pushed.
