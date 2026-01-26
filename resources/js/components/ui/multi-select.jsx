import { cn } from "@/lib/utils";
import { ChevronDown, Loader2, Search, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

export const MultiSelect = ({
  options = [],
  placeholder = "Select options",
  searchBoxPlaceholder = "Search...",
  onChange,
  searchable = true,
  onSearch,
  debounceTime = 300,
  loading = false
}) => {
  const [isOpen, setIsOpen] = useState(false);
  const [selected, setSelected] = useState([]);
  const [searchQuery, setSearchQuery] = useState('');

  const searchTimeoutRef = useRef(null);
  const prevSearchQueryRef = useRef('');

  const searchInputRef = useRef(null);
  const hiddenInputRef = useRef(null);

  const isGrouped = options.length > 0 && 'group' in options[0];

  // Focus search input when dropdown opens
  useEffect(() => {
    if (isOpen && searchable && searchInputRef.current) {
      searchInputRef.current.focus();
    }
  }, [isOpen, searchable]);

  // Call onSearch when search query changes
  useEffect(() => {
    if (!onSearch || !isOpen) return;
    if (prevSearchQueryRef.current === searchQuery) return;

    prevSearchQueryRef.current = searchQuery;
    if (searchTimeoutRef.current) {
      clearTimeout(searchTimeoutRef.current);
    }

    // Debounce the search
    searchTimeoutRef.current = setTimeout(() => {
      onSearch(searchQuery);
    }, debounceTime);

    return () => {
      if (searchTimeoutRef.current) {
        clearTimeout(searchTimeoutRef.current);
      }
    };
  }, [searchQuery]);

  const handleHiddenInputChange = (e) => {
    const value = e.target.value;
    if (value) {
      setSearchQuery(value);
      setIsOpen(true);
      // Clear hidden input and focus search
      setTimeout(() => {
        if (searchInputRef.current) {
          searchInputRef.current.focus();
          searchInputRef.current.setSelectionRange(value.length, value.length);
        }
      }, 0);
      e.target.value = '';
    }
  };

  const toggleOption = (value) => {
    const newSelected = selected.includes(value)
      ? selected.filter(v => v !== value)
      : [...selected, value];
    setSelected(newSelected);
    onChange?.(newSelected);

    // Close dropdown and focus hidden input after selection
    setIsOpen(false);
    setSearchQuery('');
    setTimeout(() => {
      hiddenInputRef.current?.focus();
    }, 0);
  };

  const clearAll = () => {
    setSelected([]);
    onChange?.([]);
  };

  const removeOption = (value, e) => {
    e.stopPropagation();
    const newSelected = selected.filter(v => v !== value);
    setSelected(newSelected);
    onChange?.(newSelected);
  };

  // Get all options (flattened if grouped)
  const getAllOptions = () => {
    if (isGrouped) {
      return options.flatMap(group => group.options);
    }
    return options;
  };

  // Filter options based on search (only if onSearch is not provided)
  const getFilteredOptions = () => {
    // If onSearch callback is provided, parent handles filtering
    if (onSearch) {
      return options;
    }

    // Otherwise, do client-side filtering
    if (!searchQuery) return options;

    const query = searchQuery.toLowerCase();

    if (isGrouped) {
      return options
        .map(group => ({
          ...group,
          options: group.options.filter(opt =>
            opt.label.toLowerCase().includes(query)
          )
        }))
        .filter(group => group.options.length > 0);
    }

    return options.filter(opt =>
      opt.label.toLowerCase().includes(query)
    );
  };

  const filteredOptions = getFilteredOptions();
  const allOptions = getAllOptions();

  return (
    <div className="relative w-full">
      {/* Trigger Container */}
      <div
        onClick={() => setIsOpen(!isOpen)}
        className="flex h-auto min-h-10 w-full items-center justify-between rounded-md border border-slate-200 bg-white px-3 py-2 text-sm ring-offset-white placeholder:text-slate-500 hover:border-slate-300 cursor-pointer transition-all disabled:cursor-not-allowed disabled:opacity-50"
      >
        <div className="flex items-center gap-1.5 flex-wrap flex-1 mr-2">
          {selected.length === 0 ? (
            <span className="text-slate-500">{placeholder}</span>
          ) : (
            <>
              {selected.map(value => {
                const option = allOptions.find(o => o.value === value);
                if (!option) return null;

                return (
                  <span
                    key={value}
                    className="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-900 border border-slate-200"
                  >
                    {option.icon && <span className="text-sm">{option.icon}</span>}
                    {option.label}
                    <X
                      className="h-3 w-3 cursor-pointer text-slate-500 hover:text-slate-700"
                      onClick={(e) => removeOption(value, e)}
                    />
                  </span>
                );
              })}
              {/* Hidden input for cursor blinking */}
              <input
                ref={hiddenInputRef}
                type="text"
                className="w-px min-h-4! h-4! border-none! outline-none! shadow-none bg-transparent p-0 m-0 caret-slate-900"
                onInput={handleHiddenInputChange}
              />
            </>
          )}
        </div>
        <ChevronDown className={cn(
          "h-4 w-4 text-slate-500 transition-transform shrink-0 mt-0.5",
          isOpen && "rotate-180"
        )} />
      </div>

      {/* Dropdown */}
      {isOpen && (
        <>
          {/* Backdrop */}
          <div
            className="fixed inset-0 z-40"
            onClick={() => setIsOpen(false)}
          />

          <div className="absolute z-50 w-full mt-2 rounded-md border border-slate-200 bg-white shadow-md">
            {/* Search */}
            {searchable && (
              <div className="p-3 border-b border-slate-200">
                <div className="relative">
                  <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                  {loading && (
                    <Loader2 className="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 animate-spin" />
                  )}
                  <input
                    ref={searchInputRef}
                    type="text"
                    placeholder={searchBoxPlaceholder}
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    className="flex h-9 w-full rounded-md border border-slate-200 bg-white pl-9! pr-3! py-1! text-sm shadow-sm transition-colors placeholder:text-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-950"
                    onClick={(e) => e.stopPropagation()}
                  />
                </div>
              </div>
            )}

            {/* Options List */}
            <div className="max-h-80 overflow-auto p-1">
              {loading ? (
                <div className="py-6 text-center text-sm text-slate-500">
                  <Loader2 className="h-5 w-5 animate-spin mx-auto mb-2" />
                  Loading...
                </div>
              ) : filteredOptions.length === 0 ? (
                <div className="py-6 text-center text-sm text-slate-500">
                  No options found
                </div>
              ) : isGrouped ? (
                // Grouped Options
                filteredOptions.map((group, groupIdx) => (
                  <div key={groupIdx} className="mb-1 last:mb-0">
                    <div className="px-2 py-1.5">
                      <h3 className="text-xs! font-medium text-slate-400! uppercase m-0!">
                        {group.group}
                      </h3>
                    </div>
                    {group.options.map(option => {
                      const isSelected = selected.includes(option.value);
                      return (
                        <div
                          key={option.value}
                          onClick={() => toggleOption(option.value)}
                          className={cn(
                            "relative flex cursor-pointer select-none items-center rounded-sm ml-2 px-2 py-1.5 text-sm outline-none transition-colors hover:bg-slate-200/80 hover:text-slate-900",
                            isSelected && "bg-slate-200/80 text-slate-900 opacity-60",
                            !isSelected && "text-slate-700"
                          )}
                        >
                          {option.icon && (
                            <span className="mr-2 text-base">{option.icon}</span>
                          )}
                          <div className="flex-1">
                            <div className="font-normal">
                              {option.label}
                            </div>
                            {option.description && (
                              <div className="text-xs text-slate-500 mt-0.5">
                                {option.description}
                              </div>
                            )}
                          </div>
                          {isSelected && (
                            <div className="ml-2 h-1.5 w-1.5 rounded-full bg-slate-900"></div>
                          )}
                        </div>
                      );
                    })}
                  </div>
                ))
              ) : (
                // Ungrouped Options
                filteredOptions.map(option => {
                  const isSelected = selected.includes(option.value);
                  return (
                    <div
                      key={option.value}
                      onClick={() => toggleOption(option.value)}
                      className={cn(
                        "relative flex cursor-pointer select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none transition-colors hover:bg-slate-200/80 hover:text-slate-900",
                        isSelected && "bg-slate-200/80 text-slate-900 opacity-60",
                        !isSelected && "text-slate-700"
                      )}
                    >
                      {option.icon && (
                        <span className="mr-2 text-base">{option.icon}</span>
                      )}
                      <div className="flex-1">
                        <div className="font-normal">
                          {option.label}
                        </div>
                        {option.description && (
                          <div className="text-xs text-slate-500 mt-0.5">
                            {option.description}
                          </div>
                        )}
                      </div>
                      {isSelected && (
                        <div className="ml-2 h-1.5 w-1.5 rounded-full bg-slate-900"></div>
                      )}
                    </div>
                  );
                })
              )}
            </div>

            {/* Footer */}
            <div className="border-t border-slate-200 p-2 flex gap-2">
              {selected.length > 0 && (
                <button
                  onClick={clearAll}
                  className="inline-flex items-center justify-center flex-1 rounded-md text-sm font-medium ring-offset-white transition-colors focus:outline-none focus:ring-2 focus:ring-slate-950 focus:ring-offset-2 border border-slate-200 bg-white hover:bg-slate-100 hover:text-slate-900 h-9 px-3"
                >
                  Clear All
                </button>
              )}
              <button
                onClick={() => setIsOpen(false)}
                className="inline-flex items-center justify-center flex-1 rounded-md text-sm font-medium ring-offset-white transition-colors focus:outline-none focus:ring-2 focus:ring-slate-950 focus:ring-offset-2 bg-slate-900 text-slate-50 hover:bg-slate-900/90 h-9 px-3"
              >
                Done
              </button>
            </div>
          </div>
        </>
      )}
    </div>
  );
};
