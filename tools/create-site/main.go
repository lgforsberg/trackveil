package main

import (
	"database/sql"
	"flag"
	"fmt"
	"log"
	"os"

	"github.com/google/uuid"
	"github.com/joho/godotenv"
	_ "github.com/lib/pq"
)

// GenerateSiteID generates a random 32-character alphanumeric site ID
func GenerateSiteID() (string, error) {
	const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"
	result := make([]byte, 32)
	randomBytes := make([]byte, 32)

	// Read random bytes from /dev/urandom
	f, err := os.Open("/dev/urandom")
	if err != nil {
		return "", err
	}
	defer f.Close()

	_, err = f.Read(randomBytes)
	if err != nil {
		return "", err
	}

	// Convert random bytes to alphanumeric characters
	for i, b := range randomBytes {
		result[i] = chars[int(b)%len(chars)]
	}

	return string(result), nil
}

func main() {
	// Command line flags
	accountName := flag.String("account", "", "Account name (creates new if doesn't exist)")
	accountID := flag.String("account-id", "", "Existing account ID (UUID)")
	siteName := flag.String("name", "", "Site name (required)")
	siteDomain := flag.String("domain", "", "Site domain (required)")
	listAccounts := flag.Bool("list-accounts", false, "List all accounts")

	flag.Parse()

	// Load environment variables
	_ = godotenv.Load("../../api/.env")

	// Build connection string
	connStr := fmt.Sprintf(
		"host=%s port=%s user=%s password=%s dbname=%s sslmode=%s",
		os.Getenv("DB_HOST"),
		os.Getenv("DB_PORT"),
		os.Getenv("DB_USER"),
		os.Getenv("DB_PASSWORD"),
		os.Getenv("DB_NAME"),
		os.Getenv("DB_SSLMODE"),
	)

	// Connect to database
	db, err := sql.Open("postgres", connStr)
	if err != nil {
		log.Fatalf("Failed to connect to database: %v", err)
	}
	defer db.Close()

	if err := db.Ping(); err != nil {
		log.Fatalf("Failed to ping database: %v", err)
	}

	// List accounts
	if *listAccounts {
		rows, err := db.Query(`
			SELECT a.id, a.name, COUNT(s.id) as site_count
			FROM accounts a
			LEFT JOIN sites s ON a.id = s.account_id
			GROUP BY a.id, a.name
			ORDER BY a.created_at DESC
		`)
		if err != nil {
			log.Fatalf("Failed to list accounts: %v", err)
		}
		defer rows.Close()

		fmt.Println("\nAccounts:")
		fmt.Println("----------------------------------------")
		for rows.Next() {
			var id, name string
			var siteCount int
			if err := rows.Scan(&id, &name, &siteCount); err != nil {
				log.Printf("Error scanning row: %v", err)
				continue
			}
			fmt.Printf("ID: %s\nName: %s\nSites: %d\n\n", id, name, siteCount)
		}
		return
	}

	// Validate required fields for creating a site
	if *siteName == "" || *siteDomain == "" {
		fmt.Println("Usage: create-site -name <name> -domain <domain> [-account <name> | -account-id <uuid>]")
		fmt.Println("\nOptions:")
		flag.PrintDefaults()
		os.Exit(1)
	}

	// Get or create account
	var finalAccountID string

	if *accountID != "" {
		// Use existing account ID
		finalAccountID = *accountID

		// Verify it exists
		var exists bool
		err := db.QueryRow("SELECT EXISTS(SELECT 1 FROM accounts WHERE id = $1)", finalAccountID).Scan(&exists)
		if err != nil || !exists {
			log.Fatalf("Account ID %s does not exist", finalAccountID)
		}
	} else if *accountName != "" {
		// Try to find existing account by name
		err := db.QueryRow("SELECT id FROM accounts WHERE name = $1", *accountName).Scan(&finalAccountID)

		if err == sql.ErrNoRows {
			// Create new account
			finalAccountID = uuid.New().String()
			_, err = db.Exec("INSERT INTO accounts (id, name) VALUES ($1, $2)", finalAccountID, *accountName)
			if err != nil {
				log.Fatalf("Failed to create account: %v", err)
			}
			fmt.Printf("✓ Created new account: %s (ID: %s)\n", *accountName, finalAccountID)
		} else if err != nil {
			log.Fatalf("Error checking for account: %v", err)
		} else {
			fmt.Printf("✓ Using existing account: %s (ID: %s)\n", *accountName, finalAccountID)
		}
	} else {
		log.Fatal("Either -account or -account-id must be specified")
	}

	// Generate site ID
	siteID, err := GenerateSiteID()
	if err != nil {
		log.Fatalf("Failed to generate site ID: %v", err)
	}

	// Create site
	_, err = db.Exec(`
		INSERT INTO sites (id, account_id, name, domain)
		VALUES ($1, $2, $3, $4)
	`, siteID, finalAccountID, *siteName, *siteDomain)

	if err != nil {
		log.Fatalf("Failed to create site: %v", err)
	}

	// Success!
	fmt.Println("\n============================================================")
	fmt.Println("✓ Site created successfully!")
	fmt.Println("============================================================")
	fmt.Printf("\nSite ID: %s\n", siteID)
	fmt.Printf("Name: %s\n", *siteName)
	fmt.Printf("Domain: %s\n", *siteDomain)
	fmt.Printf("Account ID: %s\n", finalAccountID)

	fmt.Println("\nAdd this snippet to your website:")
	fmt.Println("----------------------------------------")
	fmt.Printf(`<script async src="https://cdn.trackveil.net/tracker.js" 
        data-site-id="%s"></script>
`, siteID)
	fmt.Println("============================================================")
}
