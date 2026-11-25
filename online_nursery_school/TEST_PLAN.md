# Test Plan

## 1. Login
![TC](ducu/test1.png)|
2. Enter valid admin credentials. Expected: redirect 
1. Open the login page.
to dashboard.
3. Enter invalid username/password. Expected: error message and no access.
4. Try empty login fields. Expected: validation error.

## 2. Dashboard

5. Log in as admin. Expected: dashboard opens with stats cards.
6. Check that total pupils, parents, classes, and fee values match the database records.
7. Confirm that recent pupils and upcoming fees are displayed.

## 3. Pupils Module

8. Add a new pupil with valid data. Expected: the new pupil is saved and appears in the table.
9. Search for a pupil using the filter form. Expected: matching results only.
10. Use the live search field. Expected: AJAX results update without page reload.
11. Edit a pupil record. Expected: saved changes appear immediately.
12. Delete a pupil record. Expected: confirmation prompt and removal from database.

## 4. Parents Module

13. Add a parent record with valid names, phone, email, and address. Expected: success message.
14. Search for a parent by name or email. Expected: filtered parent list.
15. Edit a parent record. Expected: updated information is saved.
16. Delete a parent record. Expected: record removed successfully.

## 5. Classes Module

17. Add a class with a name and teacher. Expected: class saved.
18. View the number of pupils in each class. Expected: accurate count.
19. Edit a class description. Expected: updated data appears in table.
20. Delete a class. Expected: class removed from the list.

## 6. Fees Module

21. Record a fee payment with valid amount due and amount paid. Expected: balance and status update automatically.
22. Set amount paid to 0. Expected: status shows Pending.
23. Enter partial payment. Expected: status shows Partial.
24. Enter full payment. Expected: status shows Paid.
25. Filter fees by term and status. Expected: correct subset of records.
26. Enter a negative amount paid. Expected: the browser/server rejects it and does not save the fee.
27. Enter an amount paid greater than amount due. Expected: the browser/server rejects it and does not save the fee.
28. Enter a partial payment. Expected: the remaining amount due is displayed as `amount due - amount paid`, and status is Partial.

## 7. Users Module

29. Log in as admin and create a new staff or administrator user. Expected: user is added.
30. Create a parent user and link it to a parent record. Expected: the linked parent is shown in Users.
31. Try to create a parent user without choosing a parent record. Expected: validation fails.
32. Try to assign one parent record to two parent accounts. Expected: the second assignment is rejected.
33. Try to add a user without a password. Expected: validation fails.
34. Attempt to access users module as staff. Expected: permission denied.
35. Delete a user. Expected: success message and removal from table.

## 8. Parent Portal

36. Sign in as `alice` with password `admin123`. Expected: the parent portal opens, showing only Emma Uwase and Grace Uwase.
37. Try to access the staff dashboard or submit a staff CRUD action as a parent. Expected: access is denied and the parent is returned to the portal.
38. Check pupil count and fee information for the parent account. Expected: all pupils linked to the parent record appear, with total due, paid, and still-expected amounts.

## 9. Reports Module

39. Open the reports page. Expected: totals from the database are displayed.
40. Confirm total outstanding fees and totals match the fee table.

## 10. Security Tests

41. Try to access dashboard without login. Expected: redirect to login.
42. Submit a POST request without CSRF token. Expected: security validation error.
43. Verify passwords are not stored in plain text in the database.

## 11. UI/Responsive Tests

44. View the dashboard on desktop and tablet widths. Expected: layout remains user-friendly.
45. Open the app on a mobile viewport. Expected: sidebar and forms adapt properly.
46. Check modal forms, buttons, and table actions. Expected: all actions are usable.
