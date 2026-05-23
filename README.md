# Social Scheduler

A subscription-based system for scheduling posts on Facebook .. you set the time, it publishes automatically.
Built to make it easy to add other platforms later (Instagram, Twitter, ...).

## How it works

Every minute there's an automatic check on all scheduled posts .. any post that's due gets published to the page right away.

Simple idea:
1. Connect your Facebook page via OAuth
2. Write your post and pick a time
3. The system handles the rest

## Features

- Facebook OAuth integration (token valid for 60 days)
- Schedule text, images, and videos
- Subscription plans .. each plan controls how many pages and posts a user gets
- Background processing via Redis Queue so the site stays fast
- UI built with Tailwind CSS

## Tech

- Laravel 11 / PHP 8.2
- MySQL 8
- Redis
- Docker
- Tailwind CSS + Vite

## What's coming

This is just the beginning .. I have a clear vision for where this is going.

- Instagram support (structure is already in place, just needs implementation)
- Post analytics
- Notifications when a post publishes or fails
- Post templates


Nour Balata — [GitHub](https://github.com/NourBalata)