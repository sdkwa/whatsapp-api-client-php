# Contributing to WhatsApp API Client PHP

Thank you for considering contributing to the WhatsApp API Client PHP library! We welcome contributions from the community.

## Development Setup

1. **Fork the repository** and clone it locally
2. **Install dependencies**:
   ```bash
   composer install
   ```

3. **Set up your environment**:
   ```bash
   cp .env.example .env
   # Edit .env with your test credentials
   ```

## Development Workflow

### Before making changes:
1. Create a new branch from `main`
2. Make sure all tests pass: `composer test`
3. Check code style: `composer cs-check`

### Making changes:
1. Write tests for new functionality
2. Update documentation if needed
3. Follow PSR-12 coding standards
4. Add type hints where appropriate

### Testing your changes:
```bash
# Run all tests
composer test

# Run specific test file
vendor/bin/phpunit tests/WhatsAppApiClientTest.php

# Run with coverage
vendor/bin/phpunit --coverage-html build/coverage
```

### Code quality checks:
```bash
# Check coding standards
composer cs-check

# Fix coding standards
composer cs-fix

# Run static analysis
composer analyse
```

## Code Style Guidelines

We follow PSR-12 coding standards. Key points:

- Use 4 spaces for indentation
- Use camelCase for method names
- Use PascalCase for class names
- Add type hints for all parameters and return types
- Document all public methods with PHPDoc
- Keep lines under 120 characters

## Testing

- Write unit tests for all new functionality
- Maintain at least 80% code coverage
- Use descriptive test method names
- Test both success and error cases

## Documentation

- Update README.md for new features
- Add examples for complex functionality
- Document all public methods
- Include usage examples in docblocks

## Commit Messages

Use clear, descriptive commit messages:

```
feat: add support for sending polls
fix: handle empty webhook responses
docs: update installation instructions
test: add tests for file upload functionality
```

## Pull Request Process

1. Update the README.md with details of changes if needed
2. Update the version number in composer.json following [SemVer](http://semver.org/)
3. Ensure all tests pass and code follows our style guidelines
4. Submit your pull request with a clear description of changes

## Release Process

1. Create a new release on GitHub
2. Tag the release following semantic versioning (e.g., v1.2.3)
3. The CI/CD pipeline will automatically publish to Packagist

## Code of Conduct

Please be respectful and professional in all interactions. We want to maintain a welcoming environment for all contributors.

## Questions?

If you have questions about contributing, please:
1. Check existing issues and discussions
2. Create a new issue for bugs or feature requests
3. Start a discussion for general questions

Thank you for contributing! 🎉
