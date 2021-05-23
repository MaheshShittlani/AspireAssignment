# AspireAssignment

# Installation Instruction

1. Clone the repo
    git clone https://github.com/MaheshShittlani/AspireAssignment.git

2. Move to the Directory AspireAssignment
    cd AspireAssignmen

3. Install Dependency
    composer intall

4. Create .env from .env.example
    cp .env.example .env

5. Generate application key
    php artisan key:generate

6. Open the .env file for database setup in your editor
    vi .env

7. setup database credential in .env

8. migrate database
    php artisan migrate

9. Run the application
    php artisan serve

10. Open the postman

11. Image the API collection

12. Test api via postman

Api Details
--------------------------------

1. Register - Create new user and generate a new authentication token
2. Login - Login user and generate a new authentication token
3. Logout - Logout user and remove the authentication token
4. Apply - Accept loan application by authenticated users - Authorization required
5. Update Loan Status - This api update the loan status from PENDING to APPROVED - Executed by admin - No authorization required
6. Get Loan Details - Show the complete loan details - Executed by Admin - No authorization required
7. Repay Loan amount - Pay the weekly loan amount by the authenticated users - Authorization required







