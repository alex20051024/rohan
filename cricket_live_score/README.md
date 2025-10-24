# Cricket Live Score - Full-Stack Web Application

A complete mobile-first cricket live score management system built with HTML, Tailwind CSS, JavaScript, PHP, and MySQL.

## 🎯 Features

### User Panel
- **Live Match Tracking**: Real-time cricket score updates
- **Tournament Management**: View fixtures, points table, and statistics
- **Player & Team Profiles**: Detailed stats and performance history
- **Match Scorecards**: Ball-by-ball commentary and detailed statistics
- **User Authentication**: Secure login and signup system
- **Role-Based Access**: Regular users and scorers with different permissions

### Scorer Panel
- **Live Scoring Interface**: Record balls, runs, wickets, and extras
- **Ball-by-Ball Commentary**: Auto-generated commentary for each delivery
- **Undo Functionality**: Revert the last ball if needed
- **Innings Management**: Switch between innings seamlessly
- **Match Control**: Start, pause, and end matches

### Admin Panel
- **Dashboard**: Overview of tournaments, teams, players, and matches
- **Tournament Management**: Create, edit, and delete tournaments
- **Team Management**: Add teams with logos
- **Player Management**: Assign players to teams with roles
- **Match Scheduling**: Schedule matches with venue and datetime
- **Sponsor Management**: Add and manage tournament sponsors

## 🛠️ Technology Stack

- **Frontend**: HTML5, Tailwind CSS (CDN), JavaScript
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Icons**: Font Awesome 6.5+
- **No Frameworks**: Pure PHP without Laravel, React, Vue, or jQuery

## 📁 Project Structure

```
cricket_live_score/
├── assets/
│   ├── css/
│   └── js/
│       └── script.js          # Security & UX features
├── common/
│   ├── config.php             # Database configuration
│   ├── header.php             # Common header
│   └── bottom.php             # Bottom navigation
├── admin/
│   ├── index.php              # Admin dashboard
│   ├── login.php              # Admin login
│   ├── logout.php             # Admin logout
│   ├── manage_tournaments.php
│   ├── manage_teams.php
│   ├── manage_players.php
│   ├── manage_sponsors.php
│   └── manage_matches.php
├── uploads/
│   ├── teams/                 # Team logos
│   └── sponsors/              # Sponsor logos
├── index.php                  # Homepage
├── login.php                  # User login
├── signup.php                 # User registration
├── logout.php                 # User logout
├── tournament_details.php     # Tournament details page
├── match_scoring.php          # Live scoring interface
├── scorecard.php              # Match scorecard
├── player_profile.php         # Player profile
├── team_profile.php           # Team profile
└── install.php                # Database installer
```

## 🚀 Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Web browser (Chrome, Firefox, Safari, Edge)

### Setup Instructions

1. **Clone or Download the Project**
   ```bash
   # Place the cricket_live_score folder in your web server directory
   # For XAMPP: C:/xampp/htdocs/
   # For LAMP: /var/www/html/
   ```

2. **Configure Database Credentials**
   - Open `common/config.php`
   - Update database credentials if needed (default: host=127.0.0.1, user=root, pass=root)

3. **Run Installation**
   - Open browser and navigate to: `http://localhost/cricket_live_score/install.php`
   - The installer will:
     - Create the database `cricket_live_score`
     - Create all required tables
     - Insert default admin credentials
     - Auto-delete itself after successful installation

4. **Access the Application**
   - **User Homepage**: `http://localhost/cricket_live_score/`
   - **Admin Panel**: `http://localhost/cricket_live_score/admin/`

## 🔐 Default Credentials

### Admin Login
- **Username**: admin
- **Password**: admin123

### Create User Account
- Visit the signup page to create a new user account
- Choose between "User" or "Scorer" role

## 📊 Database Schema

The application uses the following database tables:

- `users` - User accounts
- `admin` - Admin accounts
- `tournaments` - Tournament information
- `teams` - Team details
- `players` - Player information
- `matches` - Match schedule and results
- `sponsors` - Sponsor details
- `tournament_sponsors` - Tournament-sponsor relationships
- `points_table` - Tournament standings
- `scoring` - Ball-by-ball scoring data
- `batting_stats` - Batting statistics
- `bowling_stats` - Bowling statistics
- `match_scorers` - Scorer assignments

## 🎨 Design Features

- **Mobile-First Design**: Optimized for mobile devices
- **Dark Theme**: Modern dark UI with accent colors
- **Responsive Layout**: Works on all screen sizes
- **Fixed Bottom Navigation**: Easy access to main sections
- **Interactive Cards**: Hover effects and smooth transitions
- **Icon Integration**: Font Awesome icons throughout

## 🔒 Security Features

- **Password Hashing**: Using PHP's password_hash()
- **SQL Injection Prevention**: Prepared statements
- **Session Management**: Secure PHP sessions
- **Input Sanitization**: All user inputs sanitized
- **Right-Click Disabled**: Content protection
- **Text Selection Disabled**: Prevent copying
- **Zoom Disabled**: Fixed viewport scale
- **DevTools Prevention**: Basic protection

## 📱 Mobile Features

- Touch-optimized interface
- Pinch-to-zoom disabled
- Double-tap zoom prevention
- Fixed bottom navigation
- Swipe-friendly cards

## 🎯 Usage Guide

### For Users
1. **Sign Up**: Create an account (User or Scorer)
2. **Browse**: View live matches and tournaments
3. **Explore**: Check team and player profiles
4. **Follow**: Track live scores and commentary

### For Scorers
1. **Login**: Use scorer account
2. **Select Match**: Choose a live match to score
3. **Record Balls**: Input runs, wickets, extras
4. **Commentary**: Auto-generated ball-by-ball commentary
5. **Manage**: Undo mistakes, change innings, end match

### For Admins
1. **Login**: Use admin credentials
2. **Create Tournament**: Add tournament details
3. **Add Teams**: Create teams with logos
4. **Add Players**: Assign players to teams
5. **Schedule Matches**: Set date, time, venue
6. **Manage Sponsors**: Add tournament sponsors

## 🐛 Troubleshooting

### Installation Issues
- Ensure MySQL service is running
- Check database credentials in config.php
- Verify PHP extensions: mysqli, pdo_mysql

### Permission Issues
- Set write permissions on uploads/ folder
- Linux/Mac: `chmod 755 uploads/`

### Display Issues
- Clear browser cache
- Check internet connection (for CDN resources)
- Try different browser

## 📝 Development Notes

- **No AJAX**: All actions use traditional form submissions
- **Page Reloads**: Required for updating data
- **Session-Based**: User authentication via PHP sessions
- **CDN Dependencies**: Tailwind CSS and Font Awesome loaded via CDN

## 🤝 Contributing

This is a complete standalone project. Modifications can be made to:
- UI/UX design in HTML/CSS
- Scoring logic in match_scoring.php
- Database schema in install.php
- Admin features in admin/ folder

## 📄 License

This project is provided as-is for educational and commercial use.

## 👨‍💻 Developer

Built as a complete full-stack cricket management system with modern design and mobile-first approach.

---

**Version**: 1.0.0  
**Last Updated**: 2025  
**Status**: Production Ready ✅
