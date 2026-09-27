# MBG Central Kitchen

A multi-organization ERP platform for managing Central Kitchen operations for the Makan Bergizi Gratis (MBG) program.

**Stack:** Laravel 13 · PHP 8.3 · MySQL 8 · Blade · Tabler · Sanctum · Spatie Permission · Activity Log

## Current Release

**v1.0 — MBG Central Kitchen ERP**

The current main branch contains the completed operational core:

- Multi-organization access isolation
- Organization, Central Kitchen, Kitchen Unit and Warehouse management
- Supplier, School and Recipient management
- Recipient allergy filtering for packing operations
- Ingredients, Products, Units and Unit Conversions
- Menu, Recipe and Nutrition management
- Demand planning and recipe-based procurement calculation
- Purchase Request (PR), Purchase Order (PO) and Goods Receipt (GR)
- Partial receiving and partial operational workflows
- Inventory ledger with transactional locking and idempotent posting
- Batch, expiry and FEFO allocation
- Stock movements, stock opname, warehouse transfer and stock reservation/release
- Production planning and Production Orders
- Recipe-driven material requirements and production consumption
- Production output and automatic material costing
- Quality Control with PASS/FAIL/CONDITIONAL outcomes
- Packaging and distribution
- Delivery dispatch, partial/failure handling and courier GPS tracking
- Delivery photo evidence
- Waste recording
- Average costing and operational financial reports
- Workflow notifications with unread badge
- Audit log
- RBAC and organization-scoped authorization
- Sanctum API v1 for authentication, stock and courier delivery tracking
- Tabler-based responsive UI
- Automated test suite and regression coverage

## Core Workflow

```
Demand
  → Recipe Explosion
  → Purchase Request
  → Purchase Order
  → Goods Receipt
  → Inventory + Batch + FEFO
  → Production Planning
  → Production Order
  → Material Consumption
  → Quality Control
  → Packaging
  → Distribution
  → Delivery
  → School / Recipient
  → Reports + Costing + Audit
```

## Inventory Engine

All physical stock mutations are centralized in the inventory service:

- Database transactions
- `lockForUpdate()`
- Append-only inventory movement ledger
- FEFO allocation
- Batch and expiry control
- Unit conversion
- Partial operations
- Reservations
- Idempotency protection
- Stock consistency checks
- Actual production costing from ledger consumption

## API v1

Sanctum-protected API currently covers:

- Token login/logout
- Current user profile
- Stock summary
- Low-stock inventory
- Expiring stock
- Inventory movements
- Courier delivery list
- GPS delivery tracking

Stock mutations remain centralized through the web workflow so the same ledger and FEFO rules are enforced.

## Product Roadmap

The project is designed to grow from an operational kitchen ERP into a complete MBG food-production, logistics and supply-chain platform.

### 1. Planning & Forecasting

- Historical demand analytics
- Daily/weekly/monthly demand forecasting
- School-level demand prediction
- Holiday and calendar-aware forecasting
- Capacity planning
- Ingredient requirement forecasting
- Automatic replenishment recommendations
- What-if planning and scenario simulation

### 2. Advanced Procurement

- Supplier quotation management
- RFQ / tender workflow
- Supplier portal
- Supplier price history
- Contract and price-list management
- Supplier performance scorecards
- Automatic PO suggestions
- Purchase budget controls
- Three-way matching: PO → GR → invoice
- Invoice and payment workflow
- Procurement approval matrix

### 3. Warehouse / WMS

- Barcode and QR scanning
- Mobile warehouse receiving
- Put-away and picking
- Bin/rack/location management
- Cycle counting
- FEFO picking waves
- Reservation allocation
- Stock aging
- Slow-moving inventory
- Quarantine and quality-hold stock
- Lot traceability and recall
- Inter-warehouse replenishment

### 4. Production Management

- Production calendar
- Kitchen capacity planning
- Work-center management
- Staff/operator assignment
- Batch production scheduling
- Recipe versioning
- Yield variance
- Actual vs theoretical consumption
- Production downtime
- Equipment and kitchen maintenance
- Production performance dashboards

### 5. Food Safety & Quality

- HACCP-oriented controls
- Critical control points
- Temperature logs
- Cleaning and sanitation checklists
- Supplier quality inspection
- Incoming QC
- In-process QC
- Finished-goods QC
- Non-conformance management
- Corrective and preventive actions (CAPA)
- Product quarantine
- Recall management
- Full batch genealogy

### 6. Nutrition & Menu Intelligence

- Nutrition database
- Nutrient calculation per serving
- Allergen matrix
- Dietary restrictions
- Menu cycle planning
- Menu approval workflow
- Nutritional compliance reports
- Cost vs nutrition analysis
- Menu substitution suggestions
- Ingredient equivalency and approved alternatives

### 7. Distribution & Logistics

- Route planning
- Multi-stop delivery optimization
- Driver assignment
- Vehicle management
- Delivery time windows
- Proof of delivery
- Digital recipient confirmation
- Geofencing
- Live fleet map
- Delivery exception management
- Return-to-kitchen workflow
- Temperature monitoring for sensitive products

### 8. School & Recipient Portal

- School portal
- Daily order confirmation
- Recipient roster
- Attendance / meal collection recording
- Allergy alerts
- Meal distribution reconciliation
- School feedback
- Complaint and incident management
- Parent/guardian communication where applicable
- School-level dashboards

### 9. Finance & Cost Control

- Purchase invoices
- Accounts payable
- Budget vs actual
- Cost center
- Kitchen cost center
- School cost allocation
- Production cost variance
- Waste cost analysis
- Supplier spend analysis
- Period closing
- Export to accounting systems

### 10. Analytics & BI

- Executive dashboard
- KPI library
- Procurement KPIs
- Inventory KPIs
- Production KPIs
- QC KPIs
- Delivery KPIs
- Waste KPIs
- Cost KPIs
- School service-level KPIs
- Drill-down reports
- Scheduled reports
- CSV/Excel/PDF exports

### 11. Automation & AI

- Low-stock recommendations
- Expiry-risk recommendations
- Demand forecasting
- Procurement recommendations
- Supplier comparison assistance
- Production planning assistance
- Anomaly detection
- Waste pattern analysis
- Delivery delay prediction
- Natural-language operational reports
- AI assistant for querying operational data with permission-aware access

AI must remain advisory and respect organization, role and data permissions.

### 12. Mobile / PWA

- Warehouse mobile workflow
- Kitchen production mobile workflow
- QC mobile workflow
- Driver mobile app/PWA
- Barcode/QR scanner
- Offline-first delivery capture
- Background GPS tracking where explicitly enabled
- Push notifications

### 13. Integration Platform

- Public API v2
- Webhooks
- School system integration
- Government reporting integration where applicable
- Accounting integration
- Payment/banking integration
- Maps and routing providers
- Barcode/label printers
- IoT temperature sensors
- Import/export connectors

### 14. Enterprise & SaaS

- Strong multi-tenancy
- Organization subscription plans
- Feature flags
- Tenant-specific configuration
- Centralized platform administration
- Usage metering
- Audit and compliance center
- Backup/restore management
- SSO/OAuth
- 2FA
- API key management
- Webhook management
- Tenant data export
- Data retention policies

### 15. Governance & Compliance

- Document management
- SOP management
- Approval workflows
- Digital signatures
- Policy acknowledgements
- Segregation of duties
- Advanced audit trails
- Data retention
- Incident management
- Business continuity
- Disaster recovery procedures

## Development Principles

- Keep inventory mutations inside the ledger service.
- Preserve organization isolation on every business query and mutation.
- Use policies and permissions for authorization.
- Prefer transactions for state-changing business operations.
- Prevent duplicate posting with idempotency controls.
- Use FEFO for perishable stock.
- Keep business calculations testable outside controllers.
- Avoid N+1 queries and uncontrolled report queries.
- Keep the Tabler UI consistent across modules.
- Add automated tests for every critical workflow.
- Never mark a feature complete while TODO, placeholder or broken workflow paths remain.

## Quality Gate

Before a release is considered complete:

```text
composer validate
Pint
PHPUnit / php artisan test
Blade compilation
Route validation
Migration + seed validation
Authorization / organization-isolation tests
Inventory ledger consistency
Critical workflow regression
Security audit
Performance / N+1 audit
TODO / FIXME / placeholder audit
Documentation update
```

## Documentation

- Indonesian: [README.id.md](README.id.md)
- Arabic: [README.ar.md](README.ar.md)
- Technical documentation: [DOCUMENTATION.md](DOCUMENTATION.md)

## License

Proprietary project. See the repository and deployment agreement for usage rights.
