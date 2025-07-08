# Publishing Guide

## 📦 Steps to Publish to Packagist.org

### 1. Prepare Repository
Ensure your GitHub repository at `https://github.com/swissup/theme-storeviews` contains:

```
├── .github/
│   ├── ISSUE_TEMPLATE/
│   │   ├── bug_report.md
│   │   └── feature_request.md
│   └── pull_request_template.md
├── Console/
│   └── Command/
│       └── CreateThemeStoreViews.php
├── etc/
│   ├── di.xml
│   └── module.xml
├── .gitignore
├── CHANGELOG.md
├── composer.json
├── CONTRIBUTING.md
├── LICENSE
├── README.md
└── registration.php
```

### 2. Create Release
```bash
# Tag the release
git tag v1.0.0

# Push to GitHub
git push origin main --tags
```

### 3. Submit to Packagist
1. Go to [packagist.org](https://packagist.org)
2. Login with your GitHub account
3. Click "Submit" 
4. Enter: `https://github.com/swissup/theme-storeviews`
5. Click "Check"
6. If validation passes, click "Submit"

### 4. Enable Auto-Updates
1. Go to your package page on Packagist
2. Click on "GitHub Service Hook"
3. Follow instructions to enable automatic updates

### 5. Verify Installation
Test the package installation:
```bash
composer require swissup/module-theme-store-views
```

## 🎯 Package Information

- **Package Name**: `swissup/module-theme-store-views`
- **Type**: `magento2-module`
- **License**: `OSL-3.0`
- **Repository**: `https://github.com/swissup/theme-storeviews`

## 📊 Post-Publication

### Package Page
Your package will be available at:
`https://packagist.org/packages/swissup/module-theme-store-views`

### Installation Command
Users can install with:
```bash
composer require swissup/module-theme-store-views
```

### Version Updates
For future versions:
1. Update version in `composer.json`
2. Update `CHANGELOG.md`
3. Create new git tag
4. Push to GitHub
5. Packagist will auto-update (if service hook is enabled)

## 🔍 Validation Checklist

Before publishing, ensure:
- [ ] `composer.json` is valid
- [ ] All required files are present
- [ ] README.md is comprehensive
- [ ] LICENSE file exists
- [ ] Version is properly tagged
- [ ] Repository is public
- [ ] All URLs in composer.json are correct

## 📈 Promoting Your Package

- Add badges to README.md
- Share on Magento community forums
- Tweet about the release
- Add to Swissup documentation
- Submit to Magento Marketplace (optional)

Good luck with your publication! 🚀
