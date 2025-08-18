SELECT
    u.id,
    u.name,
    u.email,
    COUNT(p.id) AS products_count,
    COALESCE(SUM(p.price), 0) AS products_total_value
FROM users u
         INNER JOIN products p ON p.user_id = u.id
GROUP BY u.id, u.name, u.email
HAVING COUNT(p.id) > 0
ORDER BY u.name ASC;



SELECT
    u.id,
    u.name,
    u.email
FROM users u
         LEFT JOIN products p ON p.user_id = u.id
WHERE p.id IS NULL
ORDER BY u.name ASC;

