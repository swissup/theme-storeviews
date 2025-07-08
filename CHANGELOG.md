# Changelog

All notable changes to the Swissup Theme Store Views module will be documented in this file.

## [1.0.2] - 2025-01-08

### Enhanced
- **🎨 CLI Output**: Complete visual overhaul with colorful, professional output
- **📊 Progress Tracking**: Real-time progress bar showing processed themes count
- **📈 Live Statistics**: Display of created and skipped store counts during processing
- **🌈 Color Coding**: Green for success, yellow for warnings, red for errors
- **📋 Professional Headers**: Styled banners and boxed sections
- **⚡ Status Messages**: Clear progress indicators with emojis and colors
- **📦 Installation Feedback**: Enhanced installer output with better formatting
- **🎯 Command Execution**: Improved command output with colored formatting
- **🏁 Summary Report**: Detailed completion summary with statistics

### Technical Improvements
- Enhanced progress bar with custom formatting and live message updates
- Improved installer output clearing for better display
- Better error handling and user feedback
- Real-time counter updates throughout the process

## [1.0.1] - 2025-01-08

### Fixed
- Updated composer.json configuration for proper package management
- Resolved module installation path conflicts
- Improved documentation and validation scripts

### Added
- Enhanced package validation script
- Professional README with badges
- Complete GitHub integration templates

## [1.0.0] - 2025-01-08

### Added
- Initial release of Swissup Theme Store Views module
- Automatic store view creation for 18 Swissup themes
- Theme application to corresponding store views
- Integration with Swissup Marketplace installer system
- Support for `etc/marketplace/installer.xml` processing
- Automatic installation of theme content (CMS pages, blocks, sliders, etc.)
- `--reinstall` option to force reinstall on existing store views
- Progress bars and detailed output for user feedback
- Automatic maintenance tasks (setup:upgrade, reindex, cache clear)
- Error handling and validation
- Non-interactive execution with proper store context

### Features
- **Command**: `swissup:theme:create-storeviews`
- **Options**: `--reinstall` (`-r`)
- **Supported Themes**: All 18 major Swissup themes
- **Auto-detection**: Finds installers in both vendor/ and app/design/ directories
- **Safety**: Skips existing store views unless forced
- **Maintenance**: Complete setup upgrade and reindexing

### Technical Details
- Compatible with Magento 2.4.x
- Uses proper Magento APIs for store creation
- Integrates with Swissup Marketplace commands
- Memory-safe execution with unlimited memory for heavy operations
- Proper dependency injection and command registration
