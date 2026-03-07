# Push this project to GitHub

**Your Git is on the D: drive** (`D:\Git\bin\git.exe`). Use that path in the commands below, or add **`D:\Git\bin`** to your system PATH so you can type `git` from any terminal.

---

## 1. (Optional) Add Git on D: to your PATH

So you can use `git` instead of the full path:

1. Press **Win + R**, type `sysdm.cpl`, Enter.  
2. **Advanced** tab → **Environment Variables**.  
3. Under **User variables**, select **Path** → **Edit** → **New** → add **`D:\Git\bin`** → OK.  
4. Restart Cursor (or open a new terminal). Then you can run `git` like normal.

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

Open **Terminal** in Cursor (**View → Terminal** or `` Ctrl+` ``).  
Use **`D:\Git\bin\git.exe`** (or just **`git`** if you added it to PATH). Replace `YOUR_USERNAME` and `REPO_NAME` with your GitHub username and repo name.

**Repo is already initialized and committed.** You only need to connect and push:

```powershell
cd "C:\Users\Robyn\Documents\Photography site"
```

```powershell
D:\Git\bin\git.exe branch -M main
```

```powershell
D:\Git\bin\git.exe remote add origin https://github.com/YOUR_USERNAME/REPO_NAME.git
```

```powershell
D:\Git\bin\git.exe push -u origin main
```

- When you run `git push`, a browser or prompt may ask you to **sign in to GitHub** (or use a **Personal Access Token** instead of a password).  
- If GitHub asks for a token: go to GitHub → **Settings** → **Developer settings** → **Personal access tokens** → create a token with `repo` scope, then paste it when prompted for a password.

---

## 4. If you add D:\Git\bin to PATH

Then you can use short commands:

```powershell
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/REPO_NAME.git
git push -u origin main
```

---

## Summary

| Step            | What to do |
|-----------------|------------|
| (Optional) PATH | Add `D:\Git\bin` to user PATH so you can type `git` |
| Create repo     | GitHub → New repository (no README / .gitignore) |
| Connect & push  | `D:\Git\bin\git.exe branch -M main` → `remote add origin <URL>` → `push -u origin main` |

**Already done for you:** repo initialized, all files committed (21 files).

Your `.gitignore` already excludes `private/config.php` and `private/uploads/*`, so secrets and uploads won’t be pushed.
