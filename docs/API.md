# API plan

## Current implementation
`routes/api.php` exposes `/api/v1/health` and a Sanctum-protected `/api/user` endpoint. This is a minimal framework foundation, not the planned product API. No public portfolio/customer workflow endpoints are evidenced.

## Contract direction
Use `/api/v1`; JSON resources with stable identifiers, explicit fields, pagination for collections, validation errors using a consistent documented envelope, and deliberate HTTP status codes. Publish OpenAPI alongside implementation. Public endpoints expose published, approved content only. Customer endpoints require authentication and policy-enforced ownership; never accept owner identity as authorization from client input. Do not expose private ticket details or attachment URLs through public resources.

Proposed later resource families: public projects/services/articles; enquiry submission; authenticated booking requests and tickets/replies. Exact routes, filtering, pagination and lifecycle are OPEN until user journeys and domain rules are approved. Rate-limit public submission and auth endpoints. Keep API behavior aligned with web application rules and cover it with feature/contract tests.

Flutter is a planned consumer, not an existing app. Sanctum dependency exists but production token issuance, storage, expiry/revocation and recovery flows are not implemented or specified as complete.
