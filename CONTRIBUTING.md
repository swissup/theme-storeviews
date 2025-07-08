# Contributing to Swissup Theme Store Views

Thank you for your interest in contributing to the Swissup Theme Store Views module! We welcome contributions from the community.

## 🚀 Getting Started

### Prerequisites
- Magento 2.4.x development environment
- PHP 7.4+ or 8.x
- Composer
- Git
- Swissup themes for testing

### Development Setup
1. Fork the repository
2. Clone your fork locally
3. Install the module in your Magento development environment
4. Create a new branch for your feature/fix

```bash
git clone https://github.com/your-username/theme-storeviews.git
cd theme-storeviews
git checkout -b feature/your-feature-name
```

## 📝 Development Guidelines

### Code Style
- Follow PSR-2 coding standards
- Use meaningful variable and method names
- Add proper PHPDoc comments
- Follow Magento 2 coding standards

### Testing
- Test your changes with multiple Swissup themes
- Test both new installations and existing store views
- Verify the `--reinstall` option works correctly
- Test error handling scenarios

### Commit Messages
Use clear and descriptive commit messages:
```
feat: add support for new theme type
fix: resolve store view creation issue
docs: update README with new examples
refactor: improve installer detection logic
```

## 🐛 Reporting Issues

When reporting issues, please include:
- Magento version
- PHP version
- Module version
- Steps to reproduce
- Expected vs actual behavior
- Error messages or logs
- List of installed Swissup themes

## 💡 Suggesting Features

Before suggesting a feature:
1. Check if it already exists
2. Search existing issues and PRs
3. Consider if it fits the module's scope
4. Provide a clear use case

## 🔧 Pull Request Process

1. **Fork** the repository
2. **Create** a feature branch
3. **Make** your changes
4. **Test** thoroughly
5. **Update** documentation if needed
6. **Submit** a pull request

### PR Requirements
- [ ] Code follows project style guidelines
- [ ] Changes are tested locally
- [ ] Documentation is updated
- [ ] PR description explains the changes
- [ ] No breaking changes (or clearly documented)

## 📋 Development Checklist

### Before Submitting
- [ ] Code is properly formatted
- [ ] All functionality works as expected
- [ ] No PHP errors or warnings
- [ ] Documentation is accurate
- [ ] CHANGELOG.md is updated (for significant changes)

### Testing Scenarios
- [ ] Fresh installation
- [ ] Existing store views
- [ ] Multiple themes
- [ ] Error conditions
- [ ] Different Magento versions

## 🏗️ Module Architecture

### Key Components
- `Console/Command/CreateThemeStoreViews.php` - Main command logic
- `etc/di.xml` - Dependency injection configuration
- `etc/module.xml` - Module configuration

### Extension Points
- Theme detection logic
- Installer path resolution
- Store view naming conventions
- Error handling

## 📖 Documentation

When updating documentation:
- Keep it clear and concise
- Include code examples
- Update all relevant files (README, CHANGELOG, etc.)
- Use proper markdown formatting

## 🤝 Community

### Code of Conduct
- Be respectful and inclusive
- Provide constructive feedback
- Help others learn and grow
- Follow project guidelines

### Getting Help
- Check existing documentation
- Search issues and discussions
- Ask questions in issues
- Contact maintainers if needed

## 📄 License

By contributing, you agree that your contributions will be licensed under the same OSL-3.0 license that covers the project.

## 🙏 Recognition

Contributors will be recognized in:
- CHANGELOG.md for significant contributions
- GitHub contributors list
- Documentation credits

Thank you for helping make this module better! 🎉
