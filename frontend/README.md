# ICHRI Tunisia - Frontend Documentation

## Overview

Modern, responsive e-commerce frontend built with Bootstrap 5 and vanilla JavaScript. Features elegant design, smooth animations, and complete integration with the Laravel backend API.

## 🚀 Features

### Core Functionality
- **Homepage**: Hero slider, flash sales, trending products, categories
- **Product Catalog**: Advanced filtering, sorting, pagination
- **Product Details**: Image gallery, variants, reviews, related products
- **Shopping Cart**: Real-time updates, coupon codes, price calculations
- **Checkout**: Multi-step process with shipping and payment options
- **Authentication**: Login, registration with social options
- **User Dashboard**: Orders tracking, loyalty points, wishlist management

### Technical Highlights
- **Responsive Design**: Mobile-first approach, works on all devices
- **Modern UI**: Bootstrap 5.3.2 with custom gradients and animations
- **Multi-language**: Support for French, Arabic (RTL), and English
- **API Integration**: Complete REST API client with 70+ methods
- **Guest Support**: localStorage for cart/wishlist before login

## 📁 Project Structure

```
frontend/
├── index.html                 # Homepage
├── css/style.css             # Custom styles
├── js/                       # JavaScript modules
│   ├── config.js             # Configuration
│   ├── api.js                # API client
│   ├── main.js               # Core functionality
│   └── ...                   # Page-specific JS
└── pages/                    # HTML pages
    ├── products.html
    ├── product-detail.html
    ├── cart.html
    ├── checkout.html
    ├── login.html
    ├── register.html
    └── dashboard.html
```

## 🔧 Quick Start

1. Open `index.html` in a browser
2. Configure API URL in `js/config.js`
3. Ensure backend API is running

## 📦 Technologies

- Bootstrap 5.3.2
- Bootstrap Icons
- Swiper.js (sliders)
- AOS (animations)
- Vanilla JavaScript (ES6+)

## 🌍 Multi-language

Supports French, Arabic (RTL), and English via language switcher.

## 📱 Responsive

Mobile-first design with breakpoints for all device sizes.

---

Built with ❤️ for ICHRI Tunisia
