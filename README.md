# 🗄️ ZFS Dataset Converter for Unraid

[![Build Status](https://github.com/SplitAnAtom/zfs-dataset-converter/workflows/Build%20and%20Release/badge.svg)](https://github.com/SplitAnAtom/zfs-dataset-converter/actions)
[![Latest Release](https://img.shields.io/github/v/release/SplitAnAtom/zfs-dataset-converter)](https://github.com/SplitAnAtom/zfs-dataset-converter/releases/latest)
[![Downloads](https://img.shields.io/github/downloads/SplitAnAtom/zfs-dataset-converter/total)](https://github.com/SplitAnAtom/zfs-dataset-converter/releases)
[![License](https://img.shields.io/github/license/SplitAnAtom/zfs-dataset-converter)](LICENSE)

Convert regular folders to ZFS datasets with advanced features including resume capability, intelligent validation, and comprehensive Unraid integration.

## ✨ Features

### 🔄 **Resume Interrupted Operations**
- Automatically detects and resumes interrupted conversions
- Safe cleanup of temporary directories
- No data loss from power outages or system reboots

### 🧠 **Intelligent Validation System**
- Realistic validation that allows for normal filesystem differences
- No more false "validation failed" errors
- Handles metadata files, hidden files, and system artifacts correctly

### 🔔 **Comprehensive Notifications**
- Configurable Unraid notifications for every operation
- Real-time status updates via web interface
- Mobile alerts through Unraid's notification system

### 📊 **Real-time Monitoring**
- Live progress tracking with file counts and sizes
- Visual progress bars and status indicators
- Streaming log output with syntax highlighting

### 🐳 **Container & VM Awareness**
- Automatically stops/starts Docker containers during appdata conversion
- Graceful VM shutdown during domains conversion
- Only affects containers/VMs that need conversion

### 📁 **Advanced Folder Management**
- Handles special characters and international names
- Normalizes invalid characters for ZFS compatibility
- Space analysis and requirements checking

### ⚙️ **Professional GUI**
- Easy-to-use web interface integrated with Unraid
- No command-line knowledge required
- Folder scanning and preview before conversion

## 🚀 Installation

### Method 1: Plugin Manager (Recommended)

1. Go to **Plugins** → **Install Plugin**
2. Paste this URL:
   ```
   https://github.com/SplitAnAtom/zfs-dataset-converter/releases/latest/download/zfs.dataset.converter.plg
   ```
3. Click **Install**

### Method 2: Community Applications

1. Go to **Apps** tab
2. Search for "ZFS Dataset Converter"
3. Click **Install**

### Method 3: Manual Installation

1. Download the [latest .plg file](https://github.com/SplitAnAtom/zfs-dataset-converter/releases/latest/download/zfs.dataset.converter.plg)
2. Copy to `/boot/config/plugins/` on your Unraid server
3. Run: `plugin install zfs.dataset.converter.plg`

## 💻 Usage

### Quick Start

1. **Access the Plugin:**
   - Navigate to **Settings** → **Utilities** → **ZFS Dataset Converter**

2. **Configure Settings:**
   - Choose **Dry Run** for testing or **Live Run** for actual conversion
   - Enable/disable cleanup of temporary directories
   - Configure notification preferences

3. **Set Source Paths:**
   - Enable **Docker Containers** to convert appdata folders
   - Enable **Virtual Machines** to convert VM domain folders  
   - Add **Additional Datasets** as needed

4. **Preview and Convert:**
   - Click **"Scan for Convertible Folders"** to see what will be converted
   - Review space requirements and folder list
   - Click **"Start Conversion"** to begin

### Configuration Options

#### Basic Settings
- **Run Mode**: Dry Run (testing) vs Live Run (actual conversion)
- **Enable Cleanup**: Automatically remove temp directories after validation
- **Replace Spaces**: Convert spaces to underscores in dataset names
- **Buffer Zone**: Extra space required above folder size (default: 11%)

#### Source Configuration
- **Docker Containers**: Convert appdata folders to individual datasets
- **Virtual Machines**: Convert VM vdisk folders to individual datasets
- **Additional Datasets**: Add custom pools/datasets to process

#### Notification Settings
- **Script Start/Completion**: Basic operation notifications
- **Conversion Summary**: List of converted folders
- **Errors & Failures**: Critical issue alerts
- **Warnings**: Non-critical issues and space problems
- **Container/VM Operations**: When services are stopped/started
- **Resume Operations**: When interrupted conversions are resumed

## 📋 Requirements

- **Unraid 6.12+** (for ZFS support)
- **ZFS pools/datasets** already configured
- **Sufficient free space** (~2.2x folder size during conversion)
- **Root access** for container/VM management

## ⚠️ Important Notes

### Space Requirements
The conversion process requires approximately **2.2x the folder size** in free space because:
1. Original folder is renamed to `folder_temp`
2. New ZFS dataset is created and populated
3. Both exist simultaneously until validation completes
4. Temp folder is removed after successful validation

### Data Safety
- **Always test with Dry Run first**
- **Ensure adequate backups** before conversion
- **Monitor space closely** during large conversions
- **The script preserves original data** until validation passes

### Performance Considerations
- Large folders (millions of files) may take hours to convert
- SSD storage converts faster than spinning disks
- Network storage may be significantly slower
- Consider converting during low-usage periods

## 🔧 Troubleshooting

### Common Issues

#### "Insufficient Space" Errors
- **Cause**: Not enough free space for temporary copy
- **Solution**: Free up space or convert folders in smaller batches

#### "Invalid Dataset Name" Warnings
- **Cause**: Folder names contain characters invalid for ZFS
- **Solution**: Enable "Replace Spaces" or rename folders manually

#### Conversion Appears Stuck
- **Likely Cause**: Large cleanup operation in progress
- **Check**: Look for `*_temp` directories being removed
- **Solution**: Wait for cleanup to complete (can take 30+ minutes)

#### Container/VM Conversion Fails
- **Cause**: Services not properly configured
- **Solution**: Verify pool/dataset paths in plugin settings

### Getting Help

1. **Check the live logs** in the plugin interface
2. **Review notification messages** for specific error details
3. **Post in GitHub Issues** with:
   - Plugin version
   - Unraid version
   - Log excerpts
   - Screenshots of error messages

## 🛠️ Development

### Building Locally

```bash
# Clone repository
git clone https://github.com/SplitAnAtom/zfs-dataset-converter.git
cd zfs-dataset-converter

# Validate and build
make validate
make build

# Test on Unraid system
make test UNRAID_HOST=192.168.1.100
```

### Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly on Unraid
5. Submit a pull request

### Reporting Issues

Please include:
- **Plugin version** (from Settings page)
- **Unraid version**
- **ZFS version** (`zfs version`)
- **Steps to reproduce**
- **Log files** from `/tmp/zfs_converter_*.log`
- **Screenshots** of error messages

## 📝 Changelog

### v1.0.0 (Latest)
- ✅ Initial release with full GUI
- ✅ Resume interrupted conversions
- ✅ Intelligent validation system
- ✅ Comprehensive notification support
- ✅ Container/VM awareness
- ✅ Real-time progress monitoring
- ✅ Special character handling
- ✅ Space analysis and warnings

### Previous Versions
See [Releases](https://github.com/SplitAnAtom/zfs-dataset-converter/releases) for complete changelog.

## 🙏 Acknowledgments

- **SpaceInvaderOne** - Original script inspiration
- **Unraid Community** - Testing and feedback
- **ZFS Development Team** - Robust filesystem foundation

## 📄 License

This project is licensed under the GNU General Public License v2.0 - see the [LICENSE](LICENSE) file for details.

## 🔗 Links

- **Documentation**: [GitHub Wiki](https://github.com/SplitAnAtom/zfs-dataset-converter/wiki)
- **Support**: [GitHub Issues](https://github.com/SplitAnAtom/zfs-dataset-converter/issues)
- **Unraid Forum**: [Plugin Discussion](https://forums.unraid.net) (link TBD)
- **Releases**: [GitHub Releases](https://github.com/SplitAnAtom/zfs-dataset-converter/releases)

---

<div align="center">

**⭐ If this plugin helps you, please consider starring the repository! ⭐**

Made with ❤️ for the Unraid community

</div>
