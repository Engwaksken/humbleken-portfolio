# Database and domain outline (pre-migration)

This is a conceptual ERD/domain outline for review before feature migrations; it is not a schema specification or evidence tables exist. Existing repository migrations currently cover framework users, personal access tokens, cache/jobs and `is_admin` only. Do not infer product entities from this proposal.

```text
User 1──* BookingRequest
User 1──* SupportTicket 1──* TicketReply
User 1──* Enquiry (optional account association; public submission allowed)
Project *──* Category; Project *──* MediaAsset
Service *──1 ServiceCategory
Article *──* Tag; Article *──1 ArticleCategory
```

Relationships are conceptual. Before migrations decide ownership, cardinality, nullable/anonymous enquiry rules, deletion/retention, soft deletes, uniqueness, indexes, timestamps, status transitions and audit needs. Private customer content must never be reachable through public content queries. `User` represents identity; roles beyond current initial admin flag need a separately reviewed access model. Media metadata should distinguish public assets from private ticket attachments. Do not add payments, AI conversations, vector data or speculative audit tables without approved use cases.

## Candidate entities and fields (illustrative only)
`User` (identity/admin foundation); `Project` (title, slug, summary/body, publication state/date); `Category`; `Service` (description, category, publication state); `Article` (content, category/tags, status); `Enquiry` (submitted contact details/message, status); `BookingRequest` (owner, requested time/service, status); `SupportTicket` (owner, subject, status); `TicketReply` (ticket, author, body, visibility); `MediaAsset` (storage key, visibility, MIME/size metadata). Sensitive fields, legal basis and retention require owner review.

No migration should be generated from this outline before ERD review and acceptance criteria for affected workflows are approved.
