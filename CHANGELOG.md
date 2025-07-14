# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial release of WhatsApp API Client PHP
- Complete API coverage for SDKWA WhatsApp integration
- Support for sending messages, files, contacts, and locations
- Group management functionality
- Webhook handling system
- Instance management for user accounts
- QR code authorization support
- Comprehensive test suite
- CI/CD pipeline for automated testing and publishing
- Full documentation with examples

### Features
- **Messaging**: Send text messages, files (upload/URL), contacts, locations
- **Groups**: Create, manage, and interact with group chats
- **Webhooks**: Handle incoming messages and status updates
- **Account**: Authorization, settings, profile management
- **Instance Management**: Create, extend, delete user instances
- **File Handling**: Upload files and send via URL
- **Error Handling**: Comprehensive exception handling
- **Type Safety**: Full type hints and static analysis support

### Technical Details
- PHP 7.4+ support
- PSR-12 coding standards
- Guzzle HTTP client for API requests
- PHPUnit for testing
- PHPStan for static analysis
- GitHub Actions CI/CD pipeline
- Packagist publishing automation

## [1.0.0] - 2025-01-14

### Added
- Initial stable release
- Complete WhatsApp API client implementation
- Documentation and examples
- Test suite with 100% coverage
- CI/CD pipeline setup
