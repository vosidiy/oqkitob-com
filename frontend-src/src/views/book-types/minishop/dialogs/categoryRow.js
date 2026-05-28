let nextCategoryRowId = 0

export function createManageCategoryRow(category = null, productCount = 0) {
  nextCategoryRowId += 1

  return {
    id: String(category?.id ?? ''),
    is_new: category == null,
    key: `category-row-${nextCategoryRowId}`,
    name: String(category?.name ?? ''),
    product_count: Number.isFinite(Number(productCount)) ? Number(productCount) : 0,
    remove: false,
  }
}
