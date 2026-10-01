import apiFetch from '@wordpress/api-fetch'

// Full URL via `url` (never `path`): the admin rootURL middleware would double it.
const buildUrl = (endpoint, params = {}) => {
    const url = new URL(`${window.smartpay.restUrl.replace(/\/$/, '')}/${endpoint}`)
    Object.entries(params).forEach(([k, v]) => {
        if (v !== '' && v !== null && v !== undefined) url.searchParams.set(k, v)
    })
    return url.toString()
}

const request = (endpoint, { method = 'GET', data, params } = {}) =>
    apiFetch({
        url: buildUrl(endpoint, params),
        method,
        ...(data && { data }),
        headers: { 'X-WP-Nonce': window.smartpay.apiNonce },
    })

export const GetCampaigns = (params) => request('v1/campaigns', { params }).then((r) => r?.campaigns || {})
export const GetCampaign = (id) => request(`v1/campaigns/${id}`).then((r) => r?.campaign)
export const CreateCampaign = (data) => request('v1/campaigns', { method: 'POST', data })
export const UpdateCampaign = (id, data) => request(`v1/campaigns/${id}`, { method: 'PUT', data })
export const DeleteCampaign = (id) => request(`v1/campaigns/${id}`, { method: 'DELETE' })

export const GetCampaignOverview = (id, range) => request(`v1/campaigns/${id}/overview`, { params: { range } })
export const GetCampaignForms = (id) => request(`v1/campaigns/${id}/forms`)
export const AttachCampaignForms = (id, formIds) =>
    request(`v1/campaigns/${id}/forms`, { method: 'POST', data: { form_ids: formIds } })
export const DetachCampaignForm = (id, formId) => request(`v1/campaigns/${id}/forms/${formId}`, { method: 'DELETE' })
export const SetCampaignDefaultForm = (id, formId) =>
    request(`v1/campaigns/${id}/default-form`, { method: 'POST', data: { form_id: formId } })
export const GetCampaignDonors = (id, params) => request(`v1/campaigns/${id}/donors`, { params }).then((r) => r?.donors || {})

export const AssignFormsToCampaign = (ids, campaignId) =>
    request('v1/native-forms/campaign', { method: 'POST', data: { ids, campaign_id: campaignId } })
export const GetUnassignedForms = () =>
    request('v1/native-forms', { params: { campaign: 'none', per_page: 100 } }).then((r) => r?.forms?.data || [])
export const MigrateLegacyForm = (formId) => request('v1/migrate-legacy-form', { method: 'POST', data: { form_id: formId } })

export const GetDonors = (params) => request('v1/donors', { params }).then((r) => r?.donors || {})
export const GetDonor = (id) => request(`v1/donors/${id}`)
