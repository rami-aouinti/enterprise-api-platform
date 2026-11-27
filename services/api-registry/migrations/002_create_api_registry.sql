CREATE TABLE api_registry (
    id UUID PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    description VARCHAR(2000) NOT NULL DEFAULT '',
    owner VARCHAR(120) NOT NULL,
    tags JSONB NOT NULL DEFAULT '[]'::jsonb,
    lifecycle_state VARCHAR(20) NOT NULL DEFAULT 'draft',
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT api_registry_lifecycle_state_check CHECK (lifecycle_state IN ('draft', 'published', 'deprecated')),
    CONSTRAINT api_registry_name_owner_unique UNIQUE (name, owner),
    CONSTRAINT api_registry_tags_array_check CHECK (jsonb_typeof(tags) = 'array')
);
CREATE INDEX api_registry_lifecycle_state_idx ON api_registry (lifecycle_state);
CREATE INDEX api_registry_tags_gin_idx ON api_registry USING GIN (tags);
CREATE INDEX api_registry_name_lower_idx ON api_registry (lower(name));
CREATE TABLE api_registry_versions (
    id UUID PRIMARY KEY,
    api_id UUID NOT NULL REFERENCES api_registry (id) ON DELETE CASCADE,
    version VARCHAR(64) NOT NULL,
    openapi_document JSONB NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT api_registry_versions_api_version_unique UNIQUE (api_id, version),
    CONSTRAINT api_registry_versions_document_object_check CHECK (jsonb_typeof(openapi_document) = 'object')
);
CREATE INDEX api_registry_versions_api_id_idx ON api_registry_versions (api_id, created_at DESC);
