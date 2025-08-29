# Postman Collection Setup Guide

## 📥 **Import the Collection**

1. **Download the Collection**: The file `Qode-News-API.postman_collection.json` contains the complete API collection
2. **Import to Postman**: 
   - Open Postman
   - Click "Import" button
   - Select the `Qode-News-API.postman_collection.json` file
   - The collection will be imported with all endpoints organized

## 🔧 **Environment Setup**

### **Variables Configuration**
The collection uses these environment variables:

| Variable | Default Value | Description |
|----------|---------------|-------------|
| `base_url` | `http://localhost:8000` | Your API base URL |
| `access_token` | (empty) | Bearer token for authenticated requests |

### **Setting Up Environment Variables**
1. In Postman, click on the collection name
2. Go to "Variables" tab
3. Set the `base_url` to your API URL (default: `http://localhost:8000`)
4. Leave `access_token` empty initially

## 🚀 **Quick Start Guide**

### **1. Test API Connectivity**
- Run the "Test API" request first to ensure your server is running
- Should return: `{"success": true, "message": "API is working!"}`

### **2. Authentication Flow**
1. **Register a User**: Use "Register User" with unique email
2. **Login**: Use "Login User" with your credentials
3. **Copy Token**: From login response, copy the `access_token` value
4. **Set Token**: Update the `access_token` variable in collection variables

### **3. Test Authenticated Endpoints**
- All endpoints with 🔒 require the `access_token`
- The token will be automatically included in requests

## 📋 **Available Endpoints**

### **🔐 Authentication**
- `POST /auth/register` - Create new account
- `POST /auth/login` - Login and get token
- `GET /auth/userinfo` - Get user info (🔒)
- `POST /auth/logout` - Logout (🔒)

### **📰 Articles**
- `GET /articles` - List all articles
- `GET /articles/search` - Search with filters
- `GET /articles/personalized` - Personalized feed (🔒)
- `GET /articles/source/{source}` - Filter by source
- `GET /articles/category/{category}` - Filter by category
- `GET /articles/{id}` - Get specific article
- `POST /articles/refresh` - Refresh articles (🔒)

### **👤 User Preferences**
- `GET /user/preferences` - Get preferences (🔒)
- `PUT /user/preferences` - Update preferences (🔒)
- `POST /user/sources/{source}/toggle` - Toggle source (🔒)
- `POST /user/categories/{category}/toggle` - Toggle category (🔒)
- `GET /user/sources` - Get user sources (🔒)
- `GET /user/categories` - Get user categories (🔒)

## 🔍 **Testing Tips**

### **Search Parameters**
- `keyword`: Search term
- `category`: technology, science, business, entertainment, health, sports, politics
- `source`: the_guardian, new_york_times, mediastack
- `from_date` & `to_date`: YYYY-MM-DD format
- `page` & `per_page`: Pagination

### **Response Format**
All responses follow this standard format:
```json
{
    "success": true,
    "message": "Success message",
    "data": { ... },
    "meta": { ... }
}
```

### **Error Handling**
- `400`: Validation errors
- `401`: Unauthorized (missing/invalid token)
- `404`: Resource not found
- `422`: Validation failed
- `500`: Server error

## 🐳 **Docker Setup**
If using Docker with Laravel Sail:
```bash
# Start containers
./vendor/bin/sail up -d

# Check status
./vendor/bin/sail ps

# Stop containers
./vendor/bin/sail down
```

## 📝 **Example Workflow**

1. **Start your API server** (Docker or local)
2. **Import the collection** into Postman
3. **Test connectivity** with "Test API"
4. **Register a user** with unique email
5. **Login** and copy the access token
6. **Set the access token** in collection variables
7. **Test all endpoints** systematically

## 🔧 **Troubleshooting**

### **Common Issues**
- **401 Unauthorized**: Check if `access_token` is set correctly
- **404 Not Found**: Verify `base_url` is correct
- **500 Server Error**: Check if Docker containers are running
- **Connection Refused**: Ensure API server is started

### **Docker Issues**
```bash
# Check container status
./vendor/bin/sail ps

# View logs
./vendor/bin/sail logs

# Restart containers
./vendor/bin/sail restart
```

---

**Happy Testing! 🎉**
