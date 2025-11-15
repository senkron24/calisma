# Xtream Codes IPTV Panel

## Description

This project is the source code for the Xtream Codes IPTV Panel, a comprehensive management system for IPTV services. It provides a web-based interface for managing live streams, movies, series, users, and streaming servers.

**Important Note:** A significant portion of the PHP source code in this project is obfuscated using ionCube Loader. This means that most of the files are not human-readable, making it extremely difficult to modify or contribute to the core functionality.

## Features

Based on an analysis of the available source code, the panel includes the following features:

*   **Content Management:**
    *   Manage live streams, movies, and TV series.
    *   Organize content into categories and bouquets.
    *   Import content from various sources.
    *   Fetch metadata from TMDb.
*   **User Management:**
    *   Create and manage user accounts.
    *   Set up reseller and sub-reseller accounts.
    *   Manage user access and subscriptions through packages.
*   **Streaming Server Management:**
    *   Add and manage multiple streaming servers.
    *   Monitor server status and performance.
    *   Load balancing capabilities.
*   **Device Support:**
    *   Support for MAG and Enigma2 set-top boxes.
*   **Security:**
    *   IP address blocking.
    *   User-agent blocking.
    *   ISP locking.
*   **System & Automation:**
    *   Backup and restore functionality, including to Google Drive.
    *   EPG (Electronic Program Guide) management.
    *   Transcoding profiles.
    *   Ticketing system for user support.
    *   Telegram notifications for server status.
    *   Automated content scanning from watch folders.

## System Requirements

To run the Xtream Codes IPTV Panel, the following components are required:

*   **Web Server:** Nginx is used in the default configuration.
*   **PHP:** With the **ionCube Loader** extension installed.
*   **Database:** MySQL.
*   **Software:**
    *   `ffmpeg` and `ffprobe` for stream processing.
    *   Python for various backend scripts.
*   **Operating System:** A Linux-based OS is assumed.

## Getting Started

Due to the obfuscated nature of the code, setting up a development environment or making modifications is not straightforward. The primary configuration appears to be stored in an encrypted `config` file in the root directory.
