# Swissup Theme Store Views

A Magento 2 module that automatically creates store views for each Swissup theme and applies the corresponding theme to them, including running theme-specific installers.

## 🎯 What it does

This module streamlines the process of setting up multiple Swissup themes by:

- **Creating store views** for each available Swissup theme
- **Applying themes** automatically to their corresponding store views
- **Running theme installers** to install CMS content, blocks, sliders, and configurations
- **Performing maintenance** tasks like setup upgrade and reindexing

## 📋 Requirements

- Magento 2.4+ (tested on 2.4.x)
- Swissup themes installed via Composer
- PHP 7.4+ or 8.x
- Swissup Marketplace module

## 🚀 Installation

The module is already installed in your Magento instance. If you need to reinstall:

```bash
# Enable the module
php bin/magento module:enable Swissup_ThemeStoreViews

# Run setup upgrade
php bin/magento setup:upgrade

# Clear cache
php bin/magento cache:flush
```

## 📖 Usage

### Basic Command

Create store views for all Swissup themes:

```bash
php bin/magento swissup:theme:create-storeviews
```

### Command Options

| Option | Short | Description |
|--------|-------|-------------|
| `--reinstall` | `-r` | Run theme installers even for existing store views |
| `--help` | `-h` | Display help information |

### Examples

```bash
# Create store views (skips existing ones)
php bin/magento swissup:theme:create-storeviews

# Force reinstall themes for all store views
php bin/magento swissup:theme:create-storeviews --reinstall

# Show help
php bin/magento swissup:theme:create-storeviews --help
```

## 🎨 Supported Themes

The module automatically detects and processes these Swissup themes:

| Theme | Store View Code | Store View Name |
|-------|----------------|-----------------|
| Swissup/absolute | `absolute` | Absolute |
| Swissup/argento-blank | `argento_blank` | Argento Blank |
| Swissup/argento-essence | `argento_essence` | Argento Essence |
| Swissup/argento-flat | `argento_flat` | Argento Flat |
| Swissup/argento-force | `argento_force` | Argento Force |
| Swissup/argento-home | `argento_home` | Argento Home |
| Swissup/argento-luxury | `argento_luxury` | Argento Luxury |
| Swissup/argento-mall | `argento_mall` | Argento Mall |
| Swissup/argento-marketplace | `argento_marketplace` | Argento Marketplace |
| Swissup/argento-pure2 | `argento_pure2` | Argento Pure2 |
| Swissup/argento-stripes | `argento_stripes` | Argento Stripes |
| Swissup/argentobreeze-blank | `argentobreeze_blank` | Argento Breeze Blank |
| Swissup/argentobreeze-business | `argentobreeze_business` | Argento Breeze Business |
| Swissup/argentobreeze-chic | `argentobreeze_chic` | Argento Breeze Chic |
| Swissup/argentobreeze-force | `argentobreeze_force` | Argento Breeze Force |
| Swissup/argentobreeze-stripes | `argentobreeze_stripes` | Argento Breeze Stripes |
| Swissup/breeze-blank | `breeze_blank` | Breeze Blank |
| Swissup/breeze-evolution | `breeze_evolution` | Breeze Evolution |

## ⚡ Features

### 🏪 Store View Creation
- Automatically generates valid store view codes
- Uses theme titles as store view names
- Assigns to default website and store group
- Sets appropriate sort order and status

### 🎨 Theme Application
- Applies the correct theme to each store view
- Updates design configuration automatically
- Maintains theme-store view relationships

### 📦 Theme Installation
- Detects `etc/marketplace/installer.xml` files
- Runs Swissup Marketplace installers automatically
- Installs theme-specific content:
  - CMS Pages
  - CMS Blocks  
  - Sliders (EasySlider)
  - Product Attributes
  - Configuration Settings
  - Media Files
  - Product Collections

### 🔧 Maintenance Tasks
- Runs `setup:upgrade --safe-mode=1`
- Executes `indexer:reindex`
- Clears all cache types
- Provides progress feedback

### 🛡️ Safety Features
- Skips existing store views by default
- Non-interactive execution
- Error handling and validation
- Progress bars and status updates

## 🔍 How It Works

1. **Discovery**: Scans for installed Swissup themes
2. **Validation**: Checks if themes exist and are properly installed
3. **Store Creation**: Creates store views with proper codes and names
4. **Theme Application**: Assigns themes to their respective store views
5. **Content Installation**: Runs marketplace installers for theme content
6. **Maintenance**: Performs setup upgrade, reindexing, and cache clearing

## 📁 Module Structure

```
app/code/Swissup/ThemeStoreViews/
├── Console/
│   └── Command/
│       └── CreateThemeStoreViews.php    # Main command class
├── etc/
│   ├── di.xml                           # Dependency injection
│   └── module.xml                       # Module configuration
├── registration.php                     # Module registration
└── README.md                           # This documentation
```

## 🐛 Troubleshooting

### Store View Already Exists
**Issue**: Store view with the same code already exists
**Solution**: Use `--reinstall` flag to run installers on existing store views

```bash
php bin/magento swissup:theme:create-storeviews --reinstall
```

### Theme Not Found
**Issue**: Theme not detected by the module
**Solution**: Ensure the theme is properly installed and appears in:
```bash
php bin/magento theme:info
```

### Installer Not Found
**Issue**: No installer.xml found for a theme
**Solution**: This is normal - not all themes have installers. The module will skip installation for themes without `etc/marketplace/installer.xml`

### Permission Issues
**Issue**: File permission errors during installation
**Solution**: Ensure proper file permissions:
```bash
chmod -R 755 var/
chmod -R 755 pub/static/
chmod -R 755 pub/media/
```

### Memory Issues
**Issue**: PHP memory limit exceeded
**Solution**: The module automatically uses unlimited memory for heavy operations

## 📊 Output Example

```
Creating store views for Swissup themes...
  0/18 [>---------------------------]   0%

Found installer for theme 'Absolute', running installation...
Running: php bin/magento marketplace:package:install swissup/theme-frontend-absolute --store=2 --no-interaction
[notice] Processing swissup/theme-frontend-absolute
[info] Config: Update store parameters
[info] Cms Pages: Backup existing pages
[info] CMS PAGES: Create new pages
[info] Cms Blocks: Backup existing and create new blocks
[info] Product Attributes: Update attributes
[info] Resources: Copy media files
Done.

 18/18 [============================] 100%

Created 18 store views, skipped 0 existing ones.
Running setup:upgrade...
Running reindex...
Clearing cache...
Process completed successfully!
```

## 🔗 Related Commands

```bash
# List all store views
php bin/magento store:list

# Check theme installation
php bin/magento theme:info

# Manual marketplace installation
php bin/magento marketplace:package:install swissup/theme-frontend-[theme-name] --store=[store-id]

# Manual setup and maintenance
php bin/magento setup:upgrade --safe-mode=1
php bin/magento indexer:reindex
php bin/magento cache:flush
```

## 📄 License

This module follows the same license as your Magento installation and Swissup modules.

## 💬 Support

For issues related to:
- **This module**: Contact your development team
- **Swissup themes**: Visit [Swissup Documentation](https://docs.swissuplabs.com/)
- **Magento core**: Check [Magento DevDocs](https://devdocs.magento.com/)

---

**Author**: AI Assistant  
**Version**: 1.0.0  
**Compatible**: Magento 2.4.x  
**Created**: 2025
