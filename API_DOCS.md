# Xtream Codes API Documentation

This document provides a summary of the available actions in `api.php`. All actions require an active session hash and appropriate permissions.

## Base URL

All endpoints are accessed via `api.php`.

## Authentication

All requests must be authenticated with a valid session hash. The API checks for `$_SESSION["hash"]`.

---

## Endpoints

### 1. Stream Management

-   **Action:** `stream`
-   **Permissions:** Admin access with "Edit Stream" (`edit_stream`) permissions.

**Parameters:**

| Parameter     | Type    | Description                                      |
|---------------|---------|--------------------------------------------------|
| `action`      | string  | Must be set to `stream`.                         |
| `stream_id`   | integer | The ID of the stream to manage.                  |
| `server_id`   | integer | The ID of the server where the stream is located. |
| `sub`         | string  | The sub-action to perform.                       |

**Sub-Actions:**

-   `start`: Starts the stream.
-   `stop`: Stops the stream.
-   `restart`: Restarts the stream.
-   `delete`: Deletes the stream from the specified server. If it's the last server for the stream, the stream is deleted entirely.

**Example Usage:**
```
/api.php?action=stream&sub=start&stream_id=123&server_id=1
```

---

### 2. Movie Management

-   **Action:** `movie`
-   **Permissions:** Admin access with "Edit Movie" (`edit_movie`) permissions.

**Parameters:**

| Parameter     | Type    | Description                                      |
|---------------|---------|--------------------------------------------------|
| `action`      | string  | Must be set to `movie`.                          |
| `stream_id`   | integer | The ID of the movie to manage.                   |
| `server_id`   | integer | The ID of the server where the movie is located. |
| `sub`         | string  | The sub-action to perform.                       |

**Sub-Actions:**

-   `start`: Starts the movie stream (on-demand).
-   `stop`: Stops the movie stream.
-   `delete`: Deletes the movie from the server.

**Example Usage:**
```
/api.php?action=movie&sub=delete&stream_id=456&server_id=2
```

---

### 3. Episode Management

-   **Action:** `episode`
-   **Permissions:** Admin access with "Edit Episode" (`edit_episode`) permissions.

**Parameters:**

| Parameter     | Type    | Description                                      |
|---------------|---------|--------------------------------------------------|
| `action`      | string  | Must be set to `episode`.                        |
| `stream_id`   | integer | The ID of the episode's stream to manage.        |
| `server_id`   | integer | The ID of the server where the episode is located. |
| `sub`         | string  | The sub-action to perform.                       |

**Sub-Actions:**

-   `start`: Starts the episode stream (on-demand).
-   `stop`: Stops the episode stream.
-   `delete`: Deletes the episode and its associated files.

**Example Usage:**
```
/api.php?action=episode&sub=start&stream_id=789&server_id=1
```

---

### 4. User Management

-   **Action:** `user`
-   **Permissions:**
    -   Reseller: Requires ownership of the user.
    -   Admin: Requires "Edit User" (`edit_user`) permissions.

**Parameters:**

| Parameter     | Type    | Description                                      |
|---------------|---------|--------------------------------------------------|
| `action`      | string  | Must be set to `user`.                           |
| `user_id`     | integer | The ID of the user to manage.                    |
| `sub`         | string  | The sub-action to perform.                       |

**Sub-Actions:**

-   `delete`: Deletes a user account. Resellers must have `delete_users` permission.
-   `enable`: Enables a user account.
-   `disable`: Disables a user account.
-   `ban`: (Admin only) Bans a user.
-   `unban`: (Admin only) Unbans a user.
-   `kill`: Terminates all active connections for a user.
-   `resetispuser`: Resets the ISP lock for a user.
-   `lockk`: Enables the ISP lock for a user.
-   `unlockk`: Disables the ISP lock for a user.

**Example Usage:**
```
/api.php?action=user&sub=kill&user_id=101
```

---

### 5. Activity and Process Management

#### Kill User Connection

-   **Action:** `user_activity`
-   **Permissions:**
    -   Reseller: Requires ownership of the user associated with the process.
    -   Admin: Full access.
-   **Sub-Action:** `kill`
-   **Parameter:** `pid` (the process ID to kill).

#### Kill Any Process

-   **Action:** `process`
-   **Permissions:** Admin access with "Process Monitor" (`process_monitor`) permissions.
-   **Sub-Action:** None, the action itself is to kill.
-   **Parameters:** `server` (server ID), `pid` (process ID).
