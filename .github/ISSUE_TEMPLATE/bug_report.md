---
name: Bug report
about: Create a report to help us improve
title: ''
labels: ''
assignees: ''

---

# Please ensure you complete this checklist *in full*. If you fail to do so, you're wasting my time and I *will* reserve to right to close the issue without looking

**Checklist**

This package is just a wrapper for Flysystem's Azure storage backend to integrate it with Laravel's storage API. As such, many problems users may experience with it may actually be issues with Flysystem, the Azure driver for Flysystem, or Microsoft's PHP SDK. To help eliminate these issues, please confirm the following:

- [ ] I'm able to instantiate a Flysystem instance using the same credentials as passed to this package.
- [ ] The Flysystem instance works as expected, and the bug I am reporting is not reproducible when interacting directly with Flysystem
- [ ] I can instantiate the Microsoft Azure PHP SDK successfully and can interact with my file successfully using that

**Describe the bug**
A clear and concise description of what the bug is. If it throws an exception, please provide a full stack trace.

**To Reproduce**
Steps to reproduce the behavior:
1. Go to '...'
2. Click on '....'
3. Scroll down to '....'
4. See error

**Expected behavior**
A clear and concise description of what you expected to happen.

**Packages and PHP version**
 - PHP version
 - Laravel version
 - Version of this package

**Screenshots**
If applicable, add screenshots to help explain your problem.

**Desktop (please complete the following information):**
 - OS: [e.g. iOS]
 - Browser [e.g. chrome, safari]
 - Version [e.g. 22]

**Smartphone (please complete the following information):**
 - Device: [e.g. iPhone6]
 - OS: [e.g. iOS8.1]
 - Browser [e.g. stock browser, safari]
 - Version [e.g. 22]

**Additional context**
Add any other context about the problem here.
