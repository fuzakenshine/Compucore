# Compucore

## Overview

**Compucore** is an online platform designed for computer enthusiasts to gather, share knowledge, and purchase computer components. Our mission is to create a community where users can easily find and buy the latest hardware and peripherals while connecting with like-minded individuals.

## Features

- **User-Friendly Interface**: An intuitive design that makes it easy for users to navigate and find products.
- **Product Listings**: A comprehensive catalog of computer components, including CPUs, GPUs, motherboards, RAM, storage devices, and peripherals.
- **User Accounts**: Customers can create accounts to manage their orders, track shipments, and save favorite products.
- **Order Management**: Admins can manage orders, view customer details, and track order statuses.
- **Supplier Management**: A section dedicated to managing suppliers and their products.
- **Responsive Design**: The platform is optimized for both desktop and mobile devices.

## Tech Stack

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP for server-side logic
- **Database**: MySQL for data storage
- **Version Control**: Git for tracking changes and collaboration

## Installation

To set up the Compucore project locally, follow these steps:

1. **Clone the Repository**:
   ```bash
   git clone https://github.com/yourusername/compucore.git
   ```

2. **Navigate to the Project Directory**:
   ```bash
   cd compucore
   ```

3. **Set Up the Database**:
   - Create a MySQL database and import the provided SQL scripts to set up the necessary tables.

4. **Configure Database Connection**:
   - Update the `db_connect.php` file with your database credentials.

5. **Start the Local Server**:
   - Use a local server like XAMPP or MAMP to serve the files, or use PHP's built-in server:
   ```bash
   php -S localhost:8000
   ```

6. **Access the Application**:
   - Open your web browser and navigate to `http://localhost:8000` to view the application.

## Usage

- **Creating an Account**: Users can register for an account to start shopping.
- **Browsing Products**: Users can explore various categories and filter products based on specifications.
- **Adding to Cart**: Users can add products to their cart and proceed to checkout for a seamless purchasing experience.
- **Admin Dashboard**: Admins can manage users, products, orders, and suppliers through a dedicated dashboard.

## Contributing

We welcome contributions from the community! If you'd like to contribute, please follow these steps:

1. Fork the repository.
2. Create a new branch (`git checkout -b feature-branch`).
3. Make your changes and commit them (`git commit -m 'Add new feature'`).
4. Push to the branch (`git push origin feature-branch`).
5. Open a pull request.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Contact

For any inquiries or support, please contact us at [support@compucore.com](mailto:support@compucore.com).

---

Thank you for being a part of the Compucore community! Happy shopping and building!
