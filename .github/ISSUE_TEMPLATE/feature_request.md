---
name: Feature request
about: Suggest an idea for this project
title: ''
labels: ''
assignees: ''

---

# Please ensure you complete this checklist *in full*. If you fail to do so, you're wasting my time and I will reserve to right to close the issue without looking

**Checklist**

Because this project is:

* A custom driver for [Laravel's file storage API](https://laravel.com/docs/8.x/filesystem), and
* Dependent on [Flysystem's Azure driver](https://flysystem.thephpleague.com/v1/docs/adapter/azure/) and, in turn, [the Azure PHP SDK](https://github.com/Azure/azure-storage-php) to do most of the work

The scope of any changes that can be made can be quite limited. If Flysystem, the Laravel storage API or the Azure PHP SDK don't support the feature you want, then it's very unlikely it can be done. As such, please confirm the following:

- [ ] I have checked and confirmed that the Laravel storage API can support this feature
- [ ] I have checked and confirmed that Flysystem can support this feature

**Is your feature request related to a problem? Please describe.**
A clear and concise description of what the problem is. Ex. I'm always frustrated when [...]

**Describe the solution you'd like**
A clear and concise description of what you want to happen.

**Describe alternatives you've considered**
A clear and concise description of any alternative solutions or features you've considered.

**Additional context**
Add any other context or screenshots about the feature request here.
