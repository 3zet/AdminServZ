# AdminServZ

WebInterface to manage Trackmania Forever, ManiaPlanet, and Trackmania 2020 (TM2020) dedicated servers.

## Introduction

Welcome to AdminServZ, a continued evolution of AdminServ. I've now picked up maintenance independently myself, original work is by [kev717](https://github.com/kev717) and [Chris92](https://github.com/Chris92de). Feel free to submit pull requests, issues or feature requests.

## About

I decided to continue maintaining AdminServ because the codebase was stuck on old PHP, and because I actively run it on my own servers on a daily basis. AdminServZ is my effort to keep it running modernized and up to date with current PHP versions and TM2020.

## Main Features

- Compatible with TMForever, ManiaPlanet, and Trackmania 2020 (TM2020) servers
- Configure your servers online, from everywhere!
- Fully responsive, mobile-friendly interface — manage your server from your phone
- TM2020 Script Manager: upload, edit, and hot-reload gamemode scripts directly from the browser
- Inline script settings editor with live parameter descriptions and defaults, for TM2020
- Display optimizations for Team mode
- Folder-based organization of your maps
- Allows managing your MatchSettings and playlists
- Supports third-party plugins
- Easily customizable (Header logo, Website title name, color scheme, light/dark themes)

## What's New in AdminServZ

- **New looks** — refreshed the style and added new dark variants of all themes
- **Native TM2020 support**, including a dedicated Script Manager and inline script settings editor
- **New Results tab** — live standings view, handy for tournaments and events
- **Full chat formatting** — chat messages now render with the same formatting as in-game (colors, styles, etc.)
- **Mobile-first redesign** — the whole interface, including tables, forms, and navigation, now works properly on phones
- **PHP 8 compatibility** — updated from the old PHP 5.3-era codebase
- **Bug fixes** 
- **Full English translation** of the codebase — comments and some variables were previously a mix of French and English
- **General code cleanup** — removed dead code and redundant comments

## Requirements

- A web server
- PHP 8.1+
- A dedicated TM2020/TMForever/ManiaPlanet server

## How-to install

1. Clone the repo
2. Copy it on your webserver
3. Navigate to wherever you placed it in your browser
4. Done! You're ready to configure everything in your browser
5. (optional) Add the location of your TM2020/TMForever/ManiaPlanet server to the allowed `open_basedir` directories in your `php.ini`
