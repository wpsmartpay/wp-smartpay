import { DataTable } from "./data-table";
import { Header } from "./header";
import './icons';
import { StatCard } from "./stat-card";
import { StatusBadge } from "./status-badges";
import * as AlertComponents from "./ui/alert";
import * as BadgeComponents from "./ui/badge";
import * as ButtonComponents from "./ui/button";
import * as CalendarComponents from "./ui/calendar";
import * as CardComponents from "./ui/card";
import * as DialogComponents from "./ui/dialog";
import * as DropDownComponents from "./ui/dropdown-menu";
import * as InputComponents from "./ui/input";
import * as LabelComponents from "./ui/label";
import { MultiSelect } from "./ui/multi-select";
import * as PopoverComponents from "./ui/popover";
import * as SelectComponents from "./ui/select";
import * as SwitchComponents from "./ui/switch";
import * as TableComponents from "./ui/table";
import * as TextareaComponents from "./ui/textarea";

window.WPSmartPayUI = {
	...AlertComponents,
	...BadgeComponents,
	...ButtonComponents,
	...CalendarComponents,
	...CardComponents,
	...DialogComponents,
	...DropDownComponents,
	...InputComponents,
	...LabelComponents,
	...PopoverComponents,
	...SelectComponents,
	...TableComponents,
	...TextareaComponents,
	...SwitchComponents,
	DataTable,
	Header,
	StatCard,
	StatusBadge,
	MultiSelect,
}
