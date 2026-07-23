<?php
defined('ABSPATH') || exit;

require_once __DIR__ . '/integration.php';

use SmartPay\Models\Customer;
use SmartPay\Modules\Gateway\Gateway;
use SmartPay\Modules\Payment\Payment;
use SmartPay\Models\Payment as PaymentModel;
use SmartPay\Modules\Admin\Logger;

function smartpay_svg_icon()
{
    return 'data:image/svg+xml;base64,PHN2ZyB2aWV3Qm94PSIwIDAgNTkwIDYzNyIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGRlc2M9IkNyZWF0ZWQgd2l0aCBpbWFnZXRyYWNlci5qcyB2ZXJzaW9uIDEuMi42Ij48cGF0aCBmaWxsPSJyZ2IoMCwwLDApIiBzdHJva2U9InJnYigwLDAsMCkiIHN0cm9rZS13aWR0aD0iMCIgb3BhY2l0eT0iMCIgZD0iTSAwIDAgTCA1OTAgMCBMIDU5MCA2MzcgTCAwIDYzNyBMIDAgMCBaIE0gMjg2IDM0IEwgMjM4IDQ2IEwgMjI0IDUxIEwgMTEzIDgyIEwgMTAzIDg2IEwgNzQgOTMgUSA2MCA5NyA1MSAxMDYgUSAzOCAxMTYgMzQgMTM2IEwgMzQgMzUzIEwgMzQgMzU1IEwgMzcgMzc1IEwgNDQgMzk4IEwgNTkgNDI4IEwgNzMgNDQ1IEwgNzMgMTY4IEwgNzcgMTU3IEwgODYgMTQ0IEwgMTAyIDEzNSBMIDExMSAxMzMgTCAxMTMgMTMzIEwgMTE5IDEzMSBMIDE0MyAxMjkgTCAxNDUgMTI5IEwgMTQ2IDEyOCBMIDE1NCAxMjggTCAxNjMgMTI2IEwgMTcyIDEyNiBMIDE4MSAxMjQgTCAyMDcgMTIyIEwgMjA4IDEyMSBMIDIxOSAxMjEgTCAyMjkgMTE5IEwgMjM3IDExOSBMIDIzOCAxMTggTCAyNDYgMTE4IEwgMjQ3IDExNyBMIDI1NSAxMTcgTCAyNTYgMTE2IEwgMjY1IDExNiBMIDI3NSAxMTQgTCAyODMgMTE0IEwgMjg0IDExMyBMIDMxMiAxMTEgTCAzMTMgMTEwIEwgMzI5IDEwOSBMIDMzMCAxMDggTCAzMzkgMTA4IEwgMzU2IDEwNSBMIDM3NSAxMDQgTCAzNzcgMTA0IEwgMzk0IDEwMSBMIDQwMyAxMDEgTCA0MTMgOTkgTCA0MjIgOTkgTCA0MzIgOTcgTCA0NTkgOTUgTCA0NjggOTMgTCA0ODYgOTIgTCA0OTQgOTAgUSA1MDAgOTIgNDk5IDg4IEwgNDcxIDgxIEwgNDE3IDY0IEwgNDEwIDYzIEwgMzc5IDUzIEwgMzE2IDM2IFEgMzEwIDM4IDMxMSAzNSBMIDMxMCAzNSBMIDMwNyAzNCBMIDI4NiAzNCBaIE0gNTQ5IDEyMiBMIDUzMiAxMjUgTCA1MjUgMTI1IEwgNTI0IDEyNiBMIDUxNiAxMjYgTCA1MTUgMTI3IEwgNDkyIDEyOSBMIDQ5MSAxMzAgTCA0NzUgMTMxIEwgNDc0IDEzMiBMIDQyMCAxMzggTCA0MTkgMTM5IEwgNDExIDEzOSBMIDQwMyAxNDEgTCAzOTUgMTQxIEwgMzg3IDE0MyBMIDM0MCAxNDggTCAzMzIgMTUwIEwgMzI0IDE1MCBMIDMyMyAxNTEgTCAzMDEgMTUzIEwgMzAwIDE1NCBMIDI2MSAxNTggTCAyNjAgMTU5IEwgMjM4IDE2MSBMIDIyMyAxNjQgTCAxNjAgMTcxIEwgMTUzIDE3MyBMIDEzMyAxNzUgTCAxMjEgMTgxIEwgMTEyIDE5MSBMIDEwOCAyMDAgUSAxMTAgMjA2IDEwNyAyMDggTCAxMDcgMjg4IEwgMTA5IDI4OSBMIDE2MCAyODAgTCAxOTkgMjc1IEwgMjA1IDI3MyBMIDIxOSAyNzIgTCAyMzEgMjY5IEwgMjUwIDI2NyBMIDI4MiAyNjEgTCAyODkgMjYxIEwgMzA4IDI1NyBMIDMyMSAyNTYgTCAzMzQgMjUzIEwgMzQwIDI1MyBMIDM1MyAyNTAgTCAzNjYgMjQ5IEwgMzc5IDI0NiBMIDM4NSAyNDYgTCAzODYgMjQ1IEwgNDI2IDIzOSBRIDQ0NSAyMzAgNDU3IDIxNCBRIDQ1OSAyMTUgNDU4IDIxMyBMIDQ2MiAyMTAgTCA0NjMgMjA3IEwgNDQ2IDIxMCBMIDQzMiAyMTEgTCA0MzEgMjEyIEwgNDI1IDIxMSBMIDQyMSAyMDYgTCA0MjEgMTg2IEwgNDIwIDE4NSBMIDQyMCAxNzIgTCA0MjEgMTcwIEwgNDI5IDE2NSBMIDQ2NiAxNjEgTCA0NjcgMTYwIEwgNDc4IDE2MCBMIDQ4MCAxNjEgTCA0ODMgMTY5IEwgNDgzIDE5MSBRIDQ4NyAxOTIgNDg2IDE5MCBMIDQ4OSAxODggTCA0OTAgMTg1IEwgNDk3IDE4MCBRIDQ5NiAxNzcgNDk5IDE3OCBMIDUxMyAxNjQgTCA1MTMgMTYyIEwgNTE1IDE2MiBMIDUzNyAxNDAgUSA1MzYgMTM3IDUzOSAxMzggTCA1NTMgMTIzIEwgNTQ5IDEyMiBaIE0gNTU1IDE0NiBMIDU1MyAxNTEgTCA0NjEgMjYzIFEgNDYxIDI2NiA0NTYgMjY2IEwgNDU2IDI2OCBMIDM3OCAzNjIgTCAzNzYgMzYxIEwgMzUwIDMzNSBMIDMyNCAzMDggTCAzMjEgMzA3IEwgMjcwIDMxNyBMIDI2OCAzMTcgTCAyNzAgMzIyIEwgMzUyIDQzOCBMIDM1OSA0NDMgTCAzNjcgNDQyIEwgNTA3IDI2OCBRIDUwNiAyNjUgNTA5IDI2NiBMIDUyMCAyNTEgTCA1NTYgMjA4IEwgNTU2IDE0OCBMIDU1NSAxNDYgWiBNIDQ2NiAxNzggTCA0NTQgMTgxIEwgNDUwIDE4NiBMIDQ1MSAxOTQgTCA0NTUgMTk2IFEgNDY0IDE5NyA0NzAgMTk0IEwgNDczIDE5MCBMIDQ3MiAxODMgTCA0NjYgMTc4IFogTSA1NTUgMjM0IEwgMzY3IDQ5NCBMIDM2MSA0OTggTCAzNTQgNDk2IEwgMzEyIDQ1OSBRIDI5NCA0NzQgMjc0IDQ4NiBMIDI0MiA1MDIgTCAxOTQgNTE5IEwgMTYwIDUyNiBRIDE1NiA1MjUgMTU3IDUyOCBRIDE4MCA1NDcgMjA3IDU2MyBMIDIwOCA1NjYgTCAyMTAgNTY1IEwgMjI5IDU3NyBMIDI1NiA1OTEgTCAyNzggNjAwIEwgMjg5IDYwMyBMIDMwNCA2MDMgTCAzMTcgNTk5IEwgMzczIDU3MSBRIDQyNSA1MzkgNDcwIDQ5OSBRIDUwNCA0NjggNTI5IDQyOSBRIDU0MSA0MTAgNTQ5IDM4NyBMIDU1NSAzNTggTCA1NTUgMjM0IFogTSAyMTMgMzE2IEwgMTk1IDMyMCBMIDE3MiAzMjMgTCAxNjcgMzI1IEwgMTYxIDMyNSBMIDE1MCAzMjggTCAxNDUgMzI4IEwgMTM5IDMzMCBMIDEzMyAzMzAgTCAxMjIgMzMzIEwgMTA5IDMzNCBMIDEwNyAzMzYgTCAxMDcgNDcwIEwgMTEwIDQ3OCBMIDExNiA0ODMgTCAxMjcgNDg2IEwgMTUwIDQ4NSBMIDE2MiA0ODIgTCAxNzMgNDgxIEwgMjE0IDQ2OSBRIDI1OSA0NTEgMjkyIDQyMSBMIDI5MiA0MTggTCAyMzAgMzIyIFEgMjI1IDMxNSAyMTMgMzE2IFogIj48L3BhdGg+PHBhdGggZmlsbD0icmdiKDAsMCwwKSIgc3Ryb2tlPSJyZ2IoMCwwLDApIiBzdHJva2Utd2lkdGg9IjAiIG9wYWNpdHk9IjAiIGQ9Ik0gMjMxLjUgMjAzIEwgMjM2LjUgMjA0IEwgMjQwIDIwNi41IEwgMjQxIDIxMi41IEwgMjM3LjUgMjE3IEwgMjE0LjUgMjIxIEwgMTk4LjUgMjIyIEwgMTk1IDIxOS41IEwgMTk0IDIxMi41IEwgMTk3LjUgMjA4IEwgMjE2LjUgMjA2IEwgMjIzLjUgMjA0IEwgMjMwLjUgMjA0IEwgMjMxLjUgMjAzIFogIj48L3BhdGg+PHBhdGggZmlsbD0icmdiKDAsMCwwKSIgc3Ryb2tlPSJyZ2IoMCwwLDApIiBzdHJva2Utd2lkdGg9IjAiIG9wYWNpdHk9IjAiIGQ9Ik0gMTcwLjUgMjEwIFEgMTc4LjggMjA4LjcgMTgxIDIxMy41IFEgMTgyIDIyMSAxODAgMjI0IEwgMTc0LjUgMjI2IEwgMTY5LjUgMjI2IEwgMTYyLjUgMjI4IEwgMTM4LjUgMjMwIFEgMTM0LjEgMjI3LjkgMTM0IDIyMS41IEwgMTM4LjUgMjE1IEwgMTQ1LjUgMjEzIEwgMTUzLjUgMjEzIEwgMTcwLjUgMjEwIFogIj48L3BhdGg+PHBhdGggZmlsbD0icmdiKDAsMCwwKSIgc3Ryb2tlPSJyZ2IoMCwwLDApIiBzdHJva2Utd2lkdGg9IjAiIG9wYWNpdHk9IjAiIGQ9Ik0gMjAxLjUgMjM5IEwgMjEwLjUgMjM5IEwgMjEzIDI0MS41IFEgMjE1IDI1MCAyMTAuNSAyNTIgTCAxODkuNSAyNTYgTCAxODIuNSAyNTYgTCAxODEuNSAyNTcgTCAxNjYuNSAyNTggTCAxNDcuNSAyNjIgTCAxNDAuNSAyNjIgUSAxMzUuMyAyNTkgMTM3IDI1MCBMIDE0Mi41IDI0NyBMIDE1NS41IDI0NiBMIDE3MC41IDI0MyBMIDE3Ny41IDI0MyBMIDE3OC41IDI0MiBMIDE4NS41IDI0MiBMIDE5My41IDI0MCBMIDIwMC41IDI0MCBMIDIwMS41IDIzOSBaICI+PC9wYXRoPjxwYXRoIGZpbGw9InJnYig0NCw3NCwxNzkpIiBzdHJva2U9InJnYig0NCw3NCwxNzkpIiBzdHJva2Utd2lkdGg9IjAiIG9wYWNpdHk9IjAuOTkyMTU2ODYyNzQ1MDk4MSIgZD0iTSAyODkuNSAzNCBMIDMwOS41IDM1IEwgMzI5LjUgNDAgTCA0MzguNSA3MSBMIDQ2NS41IDgwIEwgNDk3IDg4IFEgNDk4LjEgOTEuNSA0OTAuNSA5MCBMIDQ4OS41IDkxIEwgNDcxLjUgOTIgTCA0NzAuNSA5MyBMIDQ0NC41IDk1IEwgNDQzLjUgOTYgTCA0MjcuNSA5NyBMIDQyNi41IDk4IEwgNDA3LjUgOTkgTCA0MDYuNSAxMDAgTCAzOTguNSAxMDAgTCAzOTcuNSAxMDEgTCAzODkuNSAxMDEgTCAzODguNSAxMDIgTCAzODAuNSAxMDIgTCAzNzAuNSAxMDQgTCAzNjEuNSAxMDQgTCAzNjAuNSAxMDUgTCAzNTIuNSAxMDUgTCAzNTEuNSAxMDYgTCAzNDMuNSAxMDYgTCAzMzMuNSAxMDggTCAzMjQuNSAxMDggTCAzMjMuNSAxMDkgTCAyOTguNSAxMTEgTCAyOTcuNSAxMTIgTCAyNzguNSAxMTMgTCAyNzcuNSAxMTQgTCAyNTAuNSAxMTYgTCAyNDkuNSAxMTcgTCAyNDEuNSAxMTcgTCAyMzIuNSAxMTkgTCAyMTIuNSAxMjAgTCAyMTEuNSAxMjEgTCAyMDMuNSAxMjEgTCAxOTQuNSAxMjMgTCAxODQuNSAxMjMgTCAxODMuNSAxMjQgTCAxMzEuNSAxMjkgTCAxMjIuNSAxMzEgTCAxMTMuNSAxMzEgUSA5MS44IDEzNC44IDgxIDE0OS41IFEgNzQuMyAxNTcuOCA3MiAxNzAuNSBMIDcxLjUgNDQzIEwgNTkgNDI2LjUgTCA0NSAzOTguNSBMIDM4IDM3Ni41IEwgMzUgMzU5LjUgTCAzNSAzNDcuNSBMIDM0IDM0Ni41IEwgMzQgMzQ0LjUgTCAzNCAxNDAuNSBMIDM2IDEyOS41IEwgNDAgMTE5LjUgTCA1My41IDEwNCBMIDc1LjUgOTMgTCA5Ny41IDg4IEwgMTI0LjUgNzkgTCAxNDUuNSA3NCBMIDE1NS41IDcwIEwgMTkwLjUgNjEgTCAyMzkuNSA0NiBMIDI4OS41IDM0IFogIj48L3BhdGg+PHBhdGggZmlsbD0icmdiKDQ0LDc0LDE3OSkiIHN0cm9rZT0icmdiKDQ0LDc0LDE3OSkiIHN0cm9rZS13aWR0aD0iMCIgb3BhY2l0eT0iMC45OTIxNTY4NjI3NDUwOTgxIiBkPSJNIDU0OC41IDEyMyBMIDU1MSAxMjMuNSBMIDQ4NS41IDE5MCBMIDQ4NCAxODkuNSBMIDQ4NCAxNzUuNSBMIDQ4MyAxNzQuNSBMIDQ4MyAxNjUuNSBMIDQ3Ny41IDE1OSBMIDQ2MS41IDE2MCBMIDQzOC41IDE2NCBMIDQzMC41IDE2NCBMIDQyMy41IDE2NiBMIDQyMCAxNjkuNSBMIDQxOSAxNzcuNSBMIDQyMCAxNzguNSBMIDQyMSAyMDcuNSBMIDQyMy41IDIxMSBRIDQyNyAyMTMuNSA0MzQuNSAyMTIgTCA0MzUuNSAyMTEgTCA0NjEuNSAyMDggTCA0NTQuNSAyMTYgTCA0MzcuNSAyMzIgUSA0MzEuOCAyMzcuMyA0MjIuNSAyMzkgTCAxMDggMjg4IEwgMTA4IDIwMi41IFEgMTExLjEgMTg4LjEgMTIxLjUgMTgxIEwgMTM1LjUgMTc1IEwgMTQ5LjUgMTc0IEwgMTcxLjUgMTcwIEwgMTc5LjUgMTcwIEwgMTgwLjUgMTY5IEwgMTk1LjUgMTY4IEwgMTk2LjUgMTY3IEwgMjQxLjUgMTYyIEwgMjQ5LjUgMTYwIEwgMjk1LjUgMTU1IEwgMzAzLjUgMTUzIEwgMzE5LjUgMTUyIEwgMzIwLjUgMTUxIEwgMzQzLjUgMTQ5IEwgMzUxLjUgMTQ3IEwgMzU4LjUgMTQ3IEwgMzU5LjUgMTQ2IEwgMzY2LjUgMTQ2IEwgMzgzLjUgMTQzIEwgMzkxLjUgMTQzIEwgMzk5LjUgMTQxIEwgNDA2LjUgMTQxIEwgNDA3LjUgMTQwIEwgNDE0LjUgMTQwIEwgNDE1LjUgMTM5IEwgNDIyLjUgMTM5IEwgNDMwLjUgMTM3IEwgNDM4LjUgMTM3IEwgNDQ2LjUgMTM1IEwgNDc4LjUgMTMyIEwgNDc5LjUgMTMxIEwgNDg2LjUgMTMxIEwgNTAzLjUgMTI4IEwgNTExLjUgMTI4IEwgNTIwLjUgMTI2IEwgNTI4LjUgMTI2IEwgNTM3LjUgMTI0IEwgNTQ3LjUgMTI0IEwgNTQ4LjUgMTIzIFogTSAyMjggMjAzIEwgMjI3IDIwNCBMIDIxMyAyMDUgTCAxOTcgMjA4IEwgMTkzIDIxNSBMIDE5NCAyMTkgTCAyMDAgMjIzIEwgMjMyIDIxOSBMIDIzOSAyMTcgUSAyNDIgMjE1IDI0MSAyMDggUSAyMzkgMjAxIDIyOCAyMDMgWiBNIDE2NiAyMTAgTCAxNTAgMjEzIEwgMTQyIDIxMyBMIDEzOCAyMTUgUSAxMzIgMjE3IDEzNCAyMjcgTCAxNDAgMjMxIEwgMTc4IDIyNiBMIDE4MiAyMjEgTCAxODEgMjEzIEwgMTc4IDIxMCBMIDE2NiAyMTAgWiBNIDIwNSAyMzggTCAxOTcgMjQwIEwgMTkwIDI0MCBMIDE4MiAyNDIgTCAxNDYgMjQ2IFEgMTM5IDI0NyAxMzYgMjUyIEwgMTM3IDI2MCBMIDE0MiAyNjMgTCAxNjUgMjU5IEwgMTk0IDI1NiBMIDIxMiAyNTIgTCAyMTQgMjQ5IEwgMjE0IDI0MyBMIDIxMiAyMzkgUSAyMTAgMjM3IDIwNSAyMzggWiAiPjwvcGF0aD48cGF0aCBmaWxsPSJyZ2IoNDQsNzQsMTc5KSIgc3Ryb2tlPSJyZ2IoNDQsNzQsMTc5KSIgc3Ryb2tlLXdpZHRoPSIwIiBvcGFjaXR5PSIwLjk5MjE1Njg2Mjc0NTA5ODEiIGQ9Ik0gNDYzLjUgMTc5IFEgNDY5LjkgMTc5LjEgNDcyIDE4My41IFEgNDczLjMgMTkxLjggNDY4LjUgMTk0IEwgNDU2LjUgMTk2IEwgNDUyIDE5NCBRIDQ1MC4xIDE5MS4zIDQ1MSAxODQuNSBMIDQ1NC41IDE4MSBMIDQ2My41IDE3OSBaICI+PC9wYXRoPjxwYXRoIGZpbGw9InJnYig0NCw3NCwxNzkpIiBzdHJva2U9InJnYig0NCw3NCwxNzkpIiBzdHJva2Utd2lkdGg9IjAiIG9wYWNpdHk9IjAuOTkyMTU2ODYyNzQ1MDk4MSIgZD0iTSA1NTMuNSAyMzcgTCA1NTUgMjUxLjUgTCA1NTQgMjU1LjUgTCA1NTUgMjU2LjUgTCA1NTUgMzA5LjUgTCA1NTUgMzExLjUgUSA1NTYuNSAzMTcgNTU0IDMxOC41IEwgNTU1IDMxOS41IEwgNTU1IDM1MC41IEwgNTU0IDM1MS41IEwgNTU0IDM2Mi41IEwgNTQ5IDM4NC41IEwgNTQ0IDM5OC41IEwgNTMyIDQyMi41IFEgNTA0LjMgNDY3LjggNDY1LjUgNTAyIFEgNDI2LjYgNTM2LjYgMzgxLjUgNTY1IFEgMzUxLjYgNTgzLjYgMzE3LjUgNTk4IEwgMzA2LjUgNjAyIEwgMjkyLjUgNjAzIEwgMjc4LjUgNjAwIEwgMjY4LjUgNTk2IEwgMjI5LjUgNTc3IFEgMTkxIDU1NSAxNTggNTI3LjUgUSAxNTcuNCA1MjQuOCAxNjMuNSA1MjYgTCAxOTguNSA1MTggTCAyNDcuNSA1MDAgUSAyODMgNDgzLjUgMzExLjUgNDYwIEwgMzUxLjUgNDk1IFEgMzU0LjQgNDk5LjEgMzYyLjUgNDk4IEwgMzY5IDQ5MS41IEwgMzg5IDQ2NC41IEwgNTUzLjUgMjM3IFogIj48L3BhdGg+PHBhdGggZmlsbD0icmdiKDQ0LDc0LDE3OSkiIHN0cm9rZT0icmdiKDQ0LDc0LDE3OSkiIHN0cm9rZS13aWR0aD0iMCIgb3BhY2l0eT0iMC45OTIxNTY4NjI3NDUwOTgxIiBkPSJNIDIwOS41IDMxNyBMIDIyNC41IDMxOCBMIDIzMiAzMjQuNSBMIDI5MiA0MTkuNSBRIDI1OC4yIDQ1Mi4yIDIwOS41IDQ3MCBMIDE3OC41IDQ3OSBMIDE1OC41IDQ4MiBMIDE1Mi41IDQ4NCBRIDE0NyA0ODIuNSAxNDUuNSA0ODUgTCAxMjMuNSA0ODUgUSAxMTUuMiA0ODMuOCAxMTEgNDc4LjUgTCAxMDggNDcyLjUgTCAxMDggMzM1IEwgMTQxLjUgMzMwIEwgMTY0LjUgMzI1IEwgMTY5LjUgMzI1IEwgMTc1LjUgMzIzIEwgMTg2LjUgMzIyIEwgMjA5LjUgMzE3IFogIj48L3BhdGg+PHBhdGggZmlsbD0icmdiKDkxLDE3OCw3MSkiIHN0cm9rZT0icmdiKDkxLDE3OCw3MSkiIHN0cm9rZS13aWR0aD0iMCIgb3BhY2l0eT0iMC45Njg2Mjc0NTA5ODAzOTIyIiBkPSJNIDU1NC41IDE0OSBMIDU1NSAyMDcuNSBRIDQ2Mi4zIDMxOS4zIDM3MyA0MzQuNSBMIDM2NS41IDQ0MiBMIDM2MC41IDQ0MyBRIDM1My42IDQ0MS40IDM1MSA0MzUuNSBMIDI2OSAzMTguNSBMIDI3OC41IDMxNSBMIDI4OS41IDMxNCBMIDI5NC41IDMxMiBRIDMwNy4yIDMxMS43IDMxNi41IDMwOCBMIDMyMi41IDMwOCBRIDM1MS4yIDMzMy45IDM3NSAzNjMgUSAzNzguNyAzNjQuNSAzNzggMzYxLjUgUSA0NjcuNyAyNTYuNyA1NTQuNSAxNDkgWiBNIDU1NCAxNzUgTCA1NTMgMTk4IEwgNTU0IDE5OCBMIDU1NCAxNzUgWiAiPjwvcGF0aD48cGF0aCBmaWxsPSJyZ2IoMTExLDIwMSw4NSkiIHN0cm9rZT0icmdiKDExMSwyMDEsODUpIiBzdHJva2Utd2lkdGg9IjAiIG9wYWNpdHk9IjAuOTg0MzEzNzI1NDkwMTk2IiBkPSJNIDU0OC41IDE1OSBMIDU0NyAxNjEuNSBMIDU0Mi41IDE2NyBMIDU0NiAxNjIuNSBMIDU0OC41IDE1OSBaICI+PC9wYXRoPjxwYXRoIGZpbGw9InJnYigxMTEsMjAxLDg1KSIgc3Ryb2tlPSJyZ2IoMTExLDIwMSw4NSkiIHN0cm9rZS13aWR0aD0iMCIgb3BhY2l0eT0iMC45ODQzMTM3MjU0OTAxOTYiIGQ9Ik0gNTUzLjUgMTc1IEwgNTU0IDE5Ny41IEwgNTUzIDE5Ny41IEwgNTUzLjUgMTc1IFogIj48L3BhdGg+PHBhdGggZmlsbD0icmdiKDExMSwyMDEsODUpIiBzdHJva2U9InJnYigxMTEsMjAxLDg1KSIgc3Ryb2tlLXdpZHRoPSIwIiBvcGFjaXR5PSIwLjk4NDMxMzcyNTQ5MDE5NiIgZD0iTSA1NTUuNSAxODUgTCA1NTYgMTk0LjUgTCA1NTUgMTk0LjUgTCA1NTUuNSAxODUgWiAiPjwvcGF0aD48cGF0aCBmaWxsPSJyZ2IoMTExLDIwMSw4NSkiIHN0cm9rZT0icmdiKDExMSwyMDEsODUpIiBzdHJva2Utd2lkdGg9IjAiIG9wYWNpdHk9IjAuOTg0MzEzNzI1NDkwMTk2IiBkPSJNIDUxOS41IDE5NCBMIDUxOSAxOTUuNSBMIDUwOS41IDIwNyBMIDUxNCAyMDEuNSBMIDUxOS41IDE5NCBaICI+PC9wYXRoPjxwYXRoIGZpbGw9InJnYigxMTEsMjAxLDg1KSIgc3Ryb2tlPSJyZ2IoMTExLDIwMSw4NSkiIHN0cm9rZS13aWR0aD0iMCIgb3BhY2l0eT0iMC45ODQzMTM3MjU0OTAxOTYiIGQ9Ik0gNTAyLjUgMjE1IEwgNTAxIDIxNy41IEwgNDk3LjUgMjIyIEwgNTAwIDIxOC41IEwgNTAyLjUgMjE1IFogIj48L3BhdGg+PHBhdGggZmlsbD0icmdiKDExMSwyMDEsODUpIiBzdHJva2U9InJnYigxMTEsMjAxLDg1KSIgc3Ryb2tlLXdpZHRoPSIwIiBvcGFjaXR5PSIwLjk4NDMxMzcyNTQ5MDE5NiIgZD0iTSA0ODcuNSAyMzMgTCA0ODggMjM0LjUgTCA0ODIgMjM5LjUgTCA0ODcuNSAyMzMgWiAiPjwvcGF0aD48cGF0aCBmaWxsPSJyZ2IoMTExLDIwMSw4NSkiIHN0cm9rZT0icmdiKDExMSwyMDEsODUpIiBzdHJva2Utd2lkdGg9IjAiIG9wYWNpdHk9IjAuOTg0MzEzNzI1NDkwMTk2IiBkPSJNIDUyNy41IDIzNyBMIDUyOCAyMzguNSBMIDUyMyAyNDIuNSBMIDUyNy41IDIzNyBaICI+PC9wYXRoPjxwYXRoIGZpbGw9InJnYigxMTEsMjAxLDg1KSIgc3Ryb2tlPSJyZ2IoMTExLDIwMSw4NSkiIHN0cm9rZS13aWR0aD0iMCIgb3BhY2l0eT0iMC45ODQzMTM3MjU0OTAxOTYiIGQ9Ik0gNTE5LjUgMjQ3IEwgNTE4IDI0OS41IEwgNTE0LjUgMjU0IEwgNTE3IDI1MC41IEwgNTE5LjUgMjQ3IFogIj48L3BhdGg+PHBhdGggZmlsbD0icmdiKDExMSwyMDEsODUpIiBzdHJva2U9InJnYigxMTEsMjAxLDg1KSIgc3Ryb2tlLXdpZHRoPSIwIiBvcGFjaXR5PSIwLjk4NDMxMzcyNTQ5MDE5NiIgZD0iTSA0NjEuNSAyNjUgTCA0NTggMjY5LjUgTCA0NTIuNSAyNzYgTCA0NTcgMjcwLjUgTCA0NjEuNSAyNjUgWiAiPjwvcGF0aD48cGF0aCBmaWxsPSJyZ2IoMTExLDIwMSw4NSkiIHN0cm9rZT0icmdiKDExMSwyMDEsODUpIiBzdHJva2Utd2lkdGg9IjAiIG9wYWNpdHk9IjAuOTg0MzEzNzI1NDkwMTk2IiBkPSJNIDMxOC41IDMxMCBRIDMyMy42IDMwOS40IDMyNSAzMTIuNSBMIDMyNy41IDMxNiBMIDMzMSAzMTguNSBMIDMyOC41IDMxNyBMIDMyNCAzMTMuNSBRIDMyNC44IDMxMS4zIDMyMi41IDMxMiBMIDMxOC41IDMxMSBMIDMxOC41IDMxMCBaICI+PC9wYXRoPjxwYXRoIGZpbGw9InJnYigxMTEsMjAxLDg1KSIgc3Ryb2tlPSJyZ2IoMTExLDIwMSw4NSkiIHN0cm9rZS13aWR0aD0iMCIgb3BhY2l0eT0iMC45ODQzMTM3MjU0OTAxOTYiIGQ9Ik0gMzQxLjUgMzMwIEwgMzQ4IDMzNS41IEwgMzQ0LjUgMzMzIEwgMzQxLjUgMzMwIFogIj48L3BhdGg+PHBhdGggZmlsbD0icmdiKDExMSwyMDEsODUpIiBzdHJva2U9InJnYigxMTEsMjAxLDg1KSIgc3Ryb2tlLXdpZHRoPSIwIiBvcGFjaXR5PSIwLjk4NDMxMzcyNTQ5MDE5NiIgZD0iTSA0NTIuNSAzMzEgTCA0NTAgMzM0LjUgTCA0NDguNSAzMzcgTCA0NDkgMzM1LjUgTCA0NTIuNSAzMzEgWiAiPjwvcGF0aD48cGF0aCBmaWxsPSJyZ2IoMTExLDIwMSw4NSkiIHN0cm9rZT0icmdiKDExMSwyMDEsODUpIiBzdHJva2Utd2lkdGg9IjAiIG9wYWNpdHk9IjAuOTg0MzEzNzI1NDkwMTk2IiBkPSJNIDMyNS41IDM5NSBMIDMzMCA0MDAuNSBMIDMyNyAzOTguNSBMIDMyNS41IDM5NSBaICI+PC9wYXRoPjxwYXRoIGZpbGw9InJnYigxMTEsMjAxLDg1KSIgc3Ryb2tlPSJyZ2IoMTExLDIwMSw4NSkiIHN0cm9rZS13aWR0aD0iMCIgb3BhY2l0eT0iMC45ODQzMTM3MjU0OTAxOTYiIGQ9Ik0gMzM3LjUgNDEyIEwgMzQ0IDQyMS41IEwgMzQzIDQyMS41IEwgMzM3LjUgNDEyIFogIj48L3BhdGg+PHBhdGggZmlsbD0icmdiKDExMSwyMDEsODUpIiBzdHJva2U9InJnYigxMTEsMjAxLDg1KSIgc3Ryb2tlLXdpZHRoPSIwIiBvcGFjaXR5PSIwLjk4NDMxMzcyNTQ5MDE5NiIgZD0iTSAzNTMuNSA0MzQgTCAzNTcuNSA0MzkgTCAzNTkgNDM5LjUgTCAzNTcuNSA0NDAgUSAzNTMuNCA0MzcuNyAzNTMuNSA0MzQgWiAiPjwvcGF0aD48L3N2Zz4=';
}

function smartpay_amount_format($amount, $currency = '')
{
    if (empty($currency)) {
        $currency = smartpay_get_option('currency', 'USD');
    }

    $symbol = smartpay_get_currency_symbol($currency);

    $position = smartpay_get_option('currency_position', 'before');

    /**
     * should check the amount is string or not
     * when updating the form amount with empty value, then it saves the empty string
     * handle the null value also
     */
    $amount = number_format((float) $amount, 2) ?? 0.00;


    if ($position == 'before') {
        switch ($currency) {
            case 'GBP':
            case 'BRL':
            case 'EUR':
            case 'USD':
            case 'AUD':
            case 'CAD':
            case 'HKD':
            case 'MXN':
            case 'NZD':
            case 'SGD':
            case 'JPY':
            case 'BDT':
                $formatted = $symbol . $amount;
                break;
            default:
                $formatted = $currency . ' ' . $amount;
                break;
        }
    } else {
        switch ($currency) {
            case 'GBP':
            case 'BRL':
            case 'EUR':
            case 'USD':
            case 'AUD':
            case 'CAD':
            case 'HKD':
            case 'MXN':
            case 'SGD':
            case 'JPY':
            case 'BDT':
                $formatted = $amount . $symbol;
                break;
            default:
                $formatted = $amount . ' ' . $currency;
                break;
        }
    }

    return $formatted;
}

function smartpay_get_option($key = '', $default = false)
{
    global $smartpay_options;
    $value = !empty($smartpay_options[$key]) ? $smartpay_options[$key] : $default;
    return $value;
}

function smartpay_get_currency_symbol($currency = '')
{
    if (empty($currency)) {
        $currency = smartpay_get_currency();
    }

    $currencies = smartpay_get_currencies();

    if (array_key_exists($currency, $currencies)) {
        return $currencies[$currency]['symbol'] ?? '&#36;';
    } else {
        return $currencies['USD']['symbol'] ?? '&#36;';
    }
}

function smartpay_get_currency()
{
    $currency = smartpay_get_option('currency', 'USD');
    return $currency;
}

function smartpay_get_currencies()
{
    static $currencies;

    if (!isset($currencies)) {
        $currencies = apply_filters(
            'smartpay_currencies',
            [
                'AED' => [
                    'name'   => __('United Arab Emirates dirham', 'smartpay'),
                    'symbol' => '&#x62f;.&#x625;'
                ],
                'AFN' => [
                    'name'   => __('Afghan afghani', 'smartpay'),
                    'symbol' => '&#x60b;'
                ],
                'ALL' => [
                    'name'   => __('Albanian lek', 'smartpay'),
                    'symbol' => 'L',
                ],
                'AMD' => [
                    'name'   => __('Armenian dram', 'smartpay'),
                    'symbol' => 'AMD',
                ],
                'ANG' => [
                    'name'   => __('Netherlands Antillean guilder', 'smartpay'),
                    'symbol' => '&fnof;',
                ],
                'AOA' => [
                    'name'   => __('Angolan kwanza', 'smartpay'),
                    'symbol' => 'Kz',
                ],
                'ARS' => [
                    'name'   => __('Argentine peso', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'AUD' => [
                    'name'   => __('Australian dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'AWG' => [
                    'name'   => __('Aruban florin', 'smartpay'),
                    'symbol' => 'Afl.',
                ],
                'AZN' => [
                    'name'   => __('Azerbaijani manat', 'smartpay'),
                    'symbol' => 'AZN',
                ],
                'BAM' => [
                    'name'   => __('Bosnia and Herzegovina convertible mark', 'smartpay'),
                    'symbol' => 'KM',
                ],
                'BBD' => [
                    'name'   => __('Barbadian dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'BDT' => [
                    'name'   => __('Bangladeshi taka', 'smartpay'),
                    'symbol' => '&#2547;&nbsp;',
                ],
                'BGN' => [
                    'name'   => __('Bulgarian lev', 'smartpay'),
                    'symbol' => '&#1083;&#1074;.',
                ],
                'BHD' => [
                    'name'   => __('Bahraini dinar', 'smartpay'),
                    'symbol' => '.&#x62f;.&#x628;',
                ],
                'BIF' => [
                    'name'   => __('Burundian franc', 'smartpay'),
                    'symbol' => 'Fr',
                ],
                'BMD' => [
                    'name'   => __('Bermudian dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'BND' => [
                    'name'   => __('Brunei dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'BOB' => [
                    'name'   => __('Bolivian boliviano', 'smartpay'),
                    'symbol' => 'Bs.',
                ],
                'BRL' => [
                    'name'   => __('Brazilian real', 'smartpay'),
                    'symbol' => '&#82;&#36;',
                ],
                'BSD' => [
                    'name'   => __('Bahamian dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'BTC' => [
                    'name'   => __('Bitcoin', 'smartpay'),
                    'symbol' => '&#3647;',
                ],
                'BTN' => [
                    'name'   => __('Bhutanese ngultrum', 'smartpay'),
                    'symbol' => 'Nu.',
                ],
                'BWP' => [
                    'name'   => __('Botswana pula', 'smartpay'),
                    'symbol' => 'P',
                ],
                'BYR' => [
                    'name'   => __('Belarusian ruble (old)', 'smartpay'),
                    'symbol' => 'Br',
                ],
                'BYN' => [
                    'name'   => __('Belarusian ruble', 'smartpay'),
                    'symbol' => 'Br',
                ],
                'BZD' => [
                    'name'   => __('Belize dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'CAD' => [
                    'name'   => __('Canadian dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'CDF' => [
                    'name'   => __('Congolese franc', 'smartpay'),
                    'symbol' => 'Fr',
                ],
                'CHF' => [
                    'name'   => __('Swiss franc', 'smartpay'),
                    'symbol' => '&#67;&#72;&#70;',
                ],
                'CLP' => [
                    'name'   => __('Chilean peso', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'CNY' => [
                    'name'   => __('Chinese yuan', 'smartpay'),
                    'symbol' => '&yen;',
                ],
                'COP' => [
                    'name'   => __('Colombian peso', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'CRC' => [
                    'name'   => __('Costa Rican col&oacute;n', 'smartpay'),
                    'symbol' => '&#x20a1;',
                ],
                'CUC' => [
                    'name'   => __('Cuban convertible peso', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'CUP' => [
                    'name'   => __('Cuban peso', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'CVE' => [
                    'name'   => __('Cape Verdean escudo', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'CZK' => [
                    'name'   => __('Czech koruna', 'smartpay'),
                    'symbol' => '&#75;&#269;',
                ],
                'DJF' => [
                    'name'   => __('Djiboutian franc', 'smartpay'),
                    'symbol' => 'Fr',
                ],
                'DKK' => [
                    'name'   => __('Danish krone', 'smartpay'),
                    'symbol' => 'DKK',
                ],
                'DOP' => [
                    'name'   => __('Dominican peso', 'smartpay'),
                    'symbol' => 'RD&#36;',
                ],
                'DZD' => [
                    'name'   => __('Algerian dinar', 'smartpay'),
                    'symbol' => '&#x62f;.&#x62c;',
                ],
                'EGP' => [
                    'name'   => __('Egyptian pound', 'smartpay'),
                    'symbol' => 'EGP',
                ],
                'ERN' => [
                    'name'   => __('Eritrean nakfa', 'smartpay'),
                    'symbol' => 'Nfk',
                ],
                'ETB' => [
                    'name'   => __('Ethiopian birr', 'smartpay'),
                    'symbol' => 'Br',
                ],
                'EUR' => [
                    'name'   => __('Euro', 'smartpay'),
                    'symbol' => '&euro;',
                ],
                'FJD' => [
                    'name'   => __('Fijian dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'FKP' => [
                    'name'   => __('Falkland Islands pound', 'smartpay'),
                    'symbol' => '&pound;',
                ],
                'GBP' => [
                    'name'   => __('Pound sterling', 'smartpay'),
                    'symbol' => '&pound;',
                ],
                'GEL' => [
                    'name'   => __('Georgian lari', 'smartpay'),
                    'symbol' => '&#x20be;',
                ],
                'GGP' => [
                    'name'   => __('Guernsey pound', 'smartpay'),
                    'symbol' => '&pound;',
                ],
                'GHS' => [
                    'name'   => __('Ghana cedi', 'smartpay'),
                    'symbol' => '&#x20b5;',
                ],
                'GIP' => [
                    'name'   => __('Gibraltar pound', 'smartpay'),
                    'symbol' => '&pound;',
                ],
                'GMD' => [
                    'name'   => __('Gambian dalasi', 'smartpay'),
                    'symbol' => 'D',
                ],
                'GNF' => [
                    'name'   => __('Guinean franc', 'smartpay'),
                    'symbol' => 'Fr',
                ],
                'GTQ' => [
                    'name'   => __('Guatemalan quetzal', 'smartpay'),
                    'symbol' => 'Q',
                ],
                'GYD' => [
                    'name'   => __('Guyanese dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'HKD' => [
                    'name'   => __('Hong Kong dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'HNL' => [
                    'name'   => __('Honduran lempira', 'smartpay'),
                    'symbol' => 'L',
                ],
                'HRK' => [
                    'name'   => __('Croatian kuna', 'smartpay'),
                    'symbol' => 'kn',
                ],
                'HTG' => [
                    'name'   => __('Haitian gourde', 'smartpay'),
                    'symbol' => 'G',
                ],
                'HUF' => [
                    'name'   => __('Hungarian forint', 'smartpay'),
                    'symbol' => '&#70;&#116;',
                ],
                'IDR' => [
                    'name'   => __('Indonesian rupiah', 'smartpay'),
                    'symbol' => 'Rp',
                ],
                'ILS' => [
                    'name'   => __('Israeli new shekel', 'smartpay'),
                    'symbol' => '&#8362;',
                ],
                'IMP' => [
                    'name'   => __('Manx pound', 'smartpay'),
                    'symbol' => '&pound;',
                ],
                'INR' => [
                    'name'   => __('Indian rupee', 'smartpay'),
                    'symbol' => '&#8377;',
                ],
                'IQD' => [
                    'name'   => __('Iraqi dinar', 'smartpay'),
                    'symbol' => '&#x639;.&#x62f;',
                ],
                'IRR' => [
                    'name'   => __('Iranian rial', 'smartpay'),
                    'symbol' => '&#xfdfc;',
                ],
                'IRT' => [
                    'name'   => __('Iranian toman', 'smartpay'),
                    'symbol' => '&#x062A;&#x0648;&#x0645;&#x0627;&#x0646;',
                ],
                'ISK' => [
                    'name'   => __('Icelandic kr&oacute;na', 'smartpay'),
                    'symbol' => 'kr.',
                ],
                'JEP' => [
                    'name'   => __('Jersey pound', 'smartpay'),
                    'symbol' => '&pound;',
                ],
                'JMD' => [
                    'name'   => __('Jamaican dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'JOD' => [
                    'name'   => __('Jordanian dinar', 'smartpay'),
                    'symbol' => '&#x62f;.&#x627;',
                ],
                'JPY' => [
                    'name'   => __('Japanese yen', 'smartpay'),
                    'symbol' => '&yen;',
                ],
                'KES' => [
                    'name'   => __('Kenyan shilling', 'smartpay'),
                    'symbol' => 'KSh',
                ],
                'KGS' => [
                    'name'   => __('Kyrgyzstani som', 'smartpay'),
                    'symbol' => '&#x441;&#x43e;&#x43c;',
                ],
                'KHR' => [
                    'name'   => __('Cambodian riel', 'smartpay'),
                    'symbol' => '&#x17db;',
                ],
                'KMF' => [
                    'name'   => __('Comorian franc', 'smartpay'),
                    'symbol' => 'Fr',
                ],
                'KPW' => [
                    'name'   => __('North Korean won', 'smartpay'),
                    'symbol' => '&#x20a9;',
                ],
                'KRW' => [
                    'name'   => __('South Korean won', 'smartpay'),
                    'symbol' => '&#8361;',
                ],
                'KWD' => [
                    'name'   => __('Kuwaiti dinar', 'smartpay'),
                    'symbol' => '&#x62f;.&#x643;',
                ],
                'KYD' => [
                    'name'   => __('Cayman Islands dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'KZT' => [
                    'name'   => __('Kazakhstani tenge', 'smartpay'),
                    'symbol' => '&#8376;',
                ],
                'LAK' => [
                    'name'   => __('Lao kip', 'smartpay'),
                    'symbol' => '&#8365;',
                ],
                'LBP' => [
                    'name'   => __('Lebanese pound', 'smartpay'),
                    'symbol' => '&#x644;.&#x644;',
                ],
                'LKR' => [
                    'name'   => __('Sri Lankan rupee', 'smartpay'),
                    'symbol' => '&#xdbb;&#xdd4;',
                ],
                'LRD' => [
                    'name'   => __('Liberian dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'LSL' => [
                    'name'   => __('Lesotho loti', 'smartpay'),
                    'symbol' => 'L',
                ],
                'LYD' => [
                    'name'   => __('Libyan dinar', 'smartpay'),
                    'symbol' => '&#x644;.&#x62f;',
                ],
                'MAD' => [
                    'name'   => __('Moroccan dirham', 'smartpay'),
                    'symbol' => '&#x62f;.&#x645;.',
                ],
                'MDL' => [
                    'name'   => __('Moldovan leu', 'smartpay'),
                    'symbol' => 'MDL',
                ],
                'MGA' => [
                    'name'   => __('Malagasy ariary', 'smartpay'),
                    'symbol' => 'Ar',
                ],
                'MKD' => [
                    'name'   => __('Macedonian denar', 'smartpay'),
                    'symbol' => '&#x434;&#x435;&#x43d;',
                ],
                'MMK' => [
                    'name'   => __('Burmese kyat', 'smartpay'),
                    'symbol' => 'Ks',
                ],
                'MNT' => [
                    'name'   => __('Mongolian t&ouml;gr&ouml;g', 'smartpay'),
                    'symbol' => '&#x20ae;',
                ],
                'MOP' => [
                    'name'   => __('Macanese pataca', 'smartpay'),
                    'symbol' => 'P',
                ],
                'MRU' => [
                    'name'   => __('Mauritanian ouguiya', 'smartpay'),
                    'symbol' => 'UM',
                ],
                'MUR' => [
                    'name'   => __('Mauritian rupee', 'smartpay'),
                    'symbol' => '&#x20a8;',
                ],
                'MVR' => [
                    'name'   => __('Maldivian rufiyaa', 'smartpay'),
                    'symbol' => '.&#x783;',
                ],
                'MWK' => [
                    'name'   => __('Malawian kwacha', 'smartpay'),
                    'symbol' => 'MK',
                ],
                'MXN' => [
                    'name'   => __('Mexican peso', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'MYR' => [
                    'name'   => __('Malaysian ringgit', 'smartpay'),
                    'symbol' => '&#82;&#77;',
                ],
                'MZN' => [
                    'name'   => __('Mozambican metical', 'smartpay'),
                    'symbol' => 'MT',
                ],
                'NAD' => [
                    'name'   => __('Namibian dollar', 'smartpay'),
                    'symbol' => 'N&#36;',
                ],
                'NGN' => [
                    'name'   => __('Nigerian naira', 'smartpay'),
                    'symbol' => '&#8358;',
                ],
                'NIO' => [
                    'name'   => __('Nicaraguan c&oacute;rdoba', 'smartpay'),
                    'symbol' => 'C&#36;',
                ],
                'NOK' => [
                    'name'   => __('Norwegian krone', 'smartpay'),
                    'symbol' => '&#107;&#114;',
                ],
                'NPR' => [
                    'name'   => __('Nepalese rupee', 'smartpay'),
                    'symbol' => '&#8360;',
                ],
                'NZD' => [
                    'name'   => __('New Zealand dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'OMR' => [
                    'name'   => __('Omani rial', 'smartpay'),
                    'symbol' => '&#x631;.&#x639;.',
                ],
                'PAB' => [
                    'name'   => __('Panamanian balboa', 'smartpay'),
                    'symbol' => 'B/.',
                ],
                'PEN' => [
                    'name'   => __('Sol', 'smartpay'),
                    'symbol' => 'S/',
                ],
                'PGK' => [
                    'name'   => __('Papua New Guinean kina', 'smartpay'),
                    'symbol' => 'K',
                ],
                'PHP' => [
                    'name'   => __('Philippine peso', 'smartpay'),
                    'symbol' => '&#8369;',
                ],
                'PKR' => [
                    'name'   => __('Pakistani rupee', 'smartpay'),
                    'symbol' => '&#8360;',
                ],
                'PLN' => [
                    'name'   => __('Polish z&#x142;oty', 'smartpay'),
                    'symbol' => '&#122;&#322;',
                ],
                'PRB' => [
                    'name'   => __('Transnistrian ruble', 'smartpay'),
                    'symbol' => '&#x440;.',
                ],
                'PYG' => [
                    'name'   => __('Paraguayan guaran&iacute;', 'smartpay'),
                    'symbol' => '&#8370;',
                ],
                'QAR' => [
                    'name'   => __('Qatari riyal', 'smartpay'),
                    'symbol' => '&#x631;.&#x642;',
                ],
                'RMB' => [
                    'name'   => __('Renminbi', 'smartpay'),
                    'symbol' => '&yen;',
                ],
                'RON' => [
                    'name'   => __('Romanian leu', 'smartpay'),
                    'symbol' => 'lei',
                ],
                'RSD' => [
                    'name'   => __('Serbian dinar', 'smartpay'),
                    'symbol' => '&#1088;&#1089;&#1076;',
                ],
                'RUB' => [
                    'name'   => __('Russian ruble', 'smartpay'),
                    'symbol' => '&#8381;',
                ],
                'RWF' => [
                    'name'   => __('Rwandan franc', 'smartpay'),
                    'symbol' => 'Fr',
                ],
                'SAR' => [
                    'name'   => __('Saudi riyal', 'smartpay'),
                    'symbol' => '&#x631;.&#x633;',
                ],
                'SBD' => [
                    'name'   => __('Solomon Islands dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'SCR' => [
                    'name'   => __('Seychellois rupee', 'smartpay'),
                    'symbol' => '&#x20a8;',
                ],
                'SDG' => [
                    'name'   => __('Sudanese pound', 'smartpay'),
                    'symbol' => '&#x62c;.&#x633;.',
                ],
                'SEK' => [
                    'name'   => __('Swedish krona', 'smartpay'),
                    'symbol' => '&#107;&#114;',
                ],
                'SGD' => [
                    'name'   => __('Singapore dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'SHP' => [
                    'name'   => __('Saint Helena pound', 'smartpay'),
                    'symbol' => '&pound;',
                ],
                'SLL' => [
                    'name'   => __('Sierra Leonean leone', 'smartpay'),
                    'symbol' => 'Le',
                ],
                'SOS' => [
                    'name'   => __('Somali shilling', 'smartpay'),
                    'symbol' => 'Sh',
                ],
                'SRD' => [
                    'name'   => __('Surinamese dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'SSP' => [
                    'name'   => __('South Sudanese pound', 'smartpay'),
                    'symbol' => '&pound;',
                ],
                'STN' => [
                    'name'   => __('S&atilde;o Tom&eacute; and Pr&iacute;ncipe dobra', 'smartpay'),
                    'symbol' => 'Db',
                ],
                'SYP' => [
                    'name'   => __('Syrian pound', 'smartpay'),
                    'symbol' => '&#x644;.&#x633;',
                ],
                'SZL' => [
                    'name'   => __('Swazi lilangeni', 'smartpay'),
                    'symbol' => 'L',
                ],
                'THB' => [
                    'name'   => __('Thai baht', 'smartpay'),
                    'symbol' => '&#3647;',
                ],
                'TJS' => [
                    'name'   => __('Tajikistani somoni', 'smartpay'),
                    'symbol' => '&#x405;&#x41c;',
                ],
                'TMT' => [
                    'name'   => __('Turkmenistan manat', 'smartpay'),
                    'symbol' => 'm',
                ],
                'TND' => [
                    'name'   => __('Tunisian dinar', 'smartpay'),
                    'symbol' => '&#x62f;.&#x62a;',
                ],
                'TOP' => [
                    'name'   => __('Tongan pa&#x2bb;anga', 'smartpay'),
                    'symbol' => 'T&#36;',
                ],
                'TRY' => [
                    'name'   => __('Turkish lira', 'smartpay'),
                    'symbol' => '&#8378;',
                ],
                'TTD' => [
                    'name'   => __('Trinidad and Tobago dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'TWD' => [
                    'name'   => __('New Taiwan dollar', 'smartpay'),
                    'symbol' => '&#78;&#84;&#36;',
                ],
                'TZS' => [
                    'name'   => __('Tanzanian shilling', 'smartpay'),
                    'symbol' => 'Sh',
                ],
                'UAH' => [
                    'name'   => __('Ukrainian hryvnia', 'smartpay'),
                    'symbol' => '&#8372;',
                ],
                'UGX' => [
                    'name'   => __('Ugandan shilling', 'smartpay'),
                    'symbol' => 'UGX',
                ],
                'USD' => [
                    'name'   => __('United States (US) dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'UYU' => [
                    'name'   => __('Uruguayan peso', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'UZS' => [
                    'name'   => __('Uzbekistani som', 'smartpay'),
                    'symbol' => 'UZS',
                ],
                'VEF' => [
                    'name'   => __('Venezuelan bol&iacute;var', 'smartpay'),
                    'symbol' => 'Bs F',
                ],
                'VES' => [
                    'name'   => __('Bol&iacute;var soberano', 'smartpay'),
                    'symbol' => 'Bs.S',
                ],
                'VND' => [
                    'name'   => __('Vietnamese &#x111;&#x1ed3;ng', 'smartpay'),
                    'symbol' => '&#8363;',
                ],
                'VUV' => [
                    'name'   => __('Vanuatu vatu', 'smartpay'),
                    'symbol' => 'Vt',
                ],
                'WST' => [
                    'name'   => __('Samoan t&#x101;l&#x101;', 'smartpay'),
                    'symbol' => 'T',
                ],
                'XAF' => [
                    'name'   => __('Central African CFA franc', 'smartpay'),
                    'symbol' => 'CFA',
                ],
                'XCD' => [
                    'name'   => __('East Caribbean dollar', 'smartpay'),
                    'symbol' => '&#36;',
                ],
                'XOF' => [
                    'name'   => __('West African CFA franc', 'smartpay'),
                    'symbol' => 'CFA',
                ],
                'XPF' => [
                    'name'   => __('CFP franc', 'smartpay'),
                    'symbol' => 'Fr',
                ],
                'YER' => [
                    'name'   => __('Yemeni rial', 'smartpay'),
                    'symbol' => '&#xfdfc;',
                ],
                'ZAR' => [
                    'name'   => __('South African rand', 'smartpay'),
                    'symbol' => '&#82;',
                ],
                'ZMW' => [
                    'name'   => __('Zambian kwacha', 'smartpay'),
                    'symbol' => 'ZK',
                ],
            ]
        );
    }

    return $currencies;
}

function smartpay_sanitize_key($key)
{
    $key = preg_replace('/[^a-zA-Z0-9_\-\.\:\/]/', '', $key);
    return $key;
}

function smartpay_payment_gateways()
{
    // Default, built-in gateways
    return apply_filters('smartpay_gateways', Gateway::gateways());
}

function smartpay_get_enabled_payment_gateways($sort = false)
{
    $gateways = smartpay_payment_gateways();

    $enabled  = (array) smartpay_get_option('gateways', false);

    $gateway_list = array();

    foreach ($gateways as $key => $gateway) {
        if (isset($enabled[$key]) && $enabled[$key] == 1) {
            $gateway_list[$key] = $gateway;
        }
    }

    if (true === $sort) {
        // Reorder our gateways so the default is first
        $default_gateway_id = smartpay_get_default_gateway();

        if (smartpay_is_gateway_active($default_gateway_id)) {
            $default_gateway    = array($default_gateway_id => $gateway_list[$default_gateway_id]);
            unset($gateway_list[$default_gateway_id]);

            $gateway_list = array_merge($default_gateway, $gateway_list);
        }
    }

    return $gateway_list;
}

function smartpay_is_gateway_active($gateway)
{
    $gateways = smartpay_get_enabled_payment_gateways();

    if (!is_array($gateways) || !count($gateways)) {
        return;
    }

    $is_active = array_key_exists($gateway, $gateways);
    return $is_active;
}

function smartpay_is_extension_active($extension)
{
    $settings = get_option('smartpay_settings');
    if (!is_array($settings['integrations']) && !count($settings['integrations'])) {
        return;
    }

    return array_key_exists($extension, $settings['integrations']);
}

function smartpay_get_default_gateway()
{
    $default = smartpay_get_option('default_gateway', 'paddle');

    if (!smartpay_is_gateway_active($default)) {
        $gateways = smartpay_get_enabled_payment_gateways();
        $gateways = array_keys($gateways);
        $default  = reset($gateways);
    }

    return $default;
}


function smartpay_get_settings()
{
    $settings = get_option('smartpay_settings');

    if (empty($settings)) {
        $general_settings = get_option('smartpay_settings_general') ? get_option('smartpay_settings_general') : [];
        $gateway_settings = get_option('smartpay_settings_gateways') ? get_option('smartpay_settings_gateways') : [];
        $email_settings   = get_option('smartpay_settings_emails') ? get_option('smartpay_settings_emails') : [];
        $license_settings = get_option('smartpay_settings_licenses') ? get_option('smartpay_settings_licenses') : [];
        $extension_settings = get_option('smartpay_settings_extensions') ? get_option('smartpay_settings_extensions') : [];

        $settings = array_merge($general_settings, $gateway_settings, $email_settings, $license_settings, $extension_settings);
        update_option('smartpay_settings', $settings);
    }
    return apply_filters('smartpay_get_settings', $settings);
}

function smartpay_get_payment_page_uri($query_string = null)
{
    $page_id = absint(smartpay_get_option('payment_page', 0));

    return smartpay_get_page_uri($page_id, $query_string);
}

function smartpay_get_page_uri($page_id, $query_string = null)
{
    $page_uri = get_permalink($page_id);

    if ($query_string) {
        $page_uri .= $query_string;
    }

    return $page_uri;
}

function smartpay_insert_payment($paymentData)
{
    return smartpay()->get(Payment::class)->insertPayment($paymentData);
}

function smartpay_is_test_mode()
{
    $is_test_mode = smartpay_get_option('test_mode', false);
    return (bool) $is_test_mode;
}

function smartpay_get_payment_success_page_uri($query_string = null)
{
    $page_id = absint(smartpay_get_option('payment_success_page', 0));

    return smartpay_get_page_uri($page_id, $query_string);
}

function smartpay_get_payment_failure_page_uri($query_string = null)
{
    $page_id = absint(smartpay_get_option('payment_failure_page', 0));

    return smartpay_get_page_uri($page_id, $query_string);
}

function smartpay_update_settings(array $settings)
{
    $old_settings = get_option('smartpay_settings');

    if (!($old_settings === $settings)) {
        update_option('smartpay_settings', $settings);
    }
}

/**
 * Get User IP
 *
 * @since 0.0.4
 * @return string $ip
 */
function smartpay_get_ip()
{
    $ip = false;

    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = filter_var(wp_unslash($_SERVER['HTTP_CLIENT_IP']), FILTER_VALIDATE_IP);
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {

        // To check ip is pass from proxy.
        // Can include more than 1 ip, first is the public one.

        $ips = explode(',', sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR'])));
        if (is_array($ips)) {
            $ip = filter_var($ips[0], FILTER_VALIDATE_IP);
        }
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = filter_var(wp_unslash($_SERVER['REMOTE_ADDR']), FILTER_VALIDATE_IP);
    }

    $ip = false !== $ip ? $ip : '127.0.0.1';

    // Fix potential CSV returned from $_SERVER variables.
    $ip_array = explode(',', $ip);
    $ip_array = array_map('trim', $ip_array);

    return apply_filters('smartpay_get_ip', $ip_array[0]);
}

/**
 * Get File Extension
 *
 * @since 0.0.4
 * @param string $str File name
 * @return string File extension
 */
function smartpay_get_file_extension($str)
{
    $parts = explode('.', $str);
    return end($parts);
}

function smartpay_get_paypal_time_option($time_option)
{
    $options = array(
        'Daily'             => 'D',
        'Weekly'            => 'w',
        'Monthly'           => 'M',
        'Every 3 Months'    => 'M',
        'Every 6 Months'    => 'M',
        'Yearly'            => 'Y',
    );

    return  $options[$time_option] ?? 'D';
}

function smartpay_get_paypal_time_duration_option($time_option)
{
    $options = array(
        'Daily'             => '1',
        'Weekly'            => '1',
        'Monthly'           => '1',
        'Every 3 Months'    => '3',
        'Every 6 Months'    => '6',
        'Yearly'            => '1',
    );

    return  $options[$time_option] ?? '1';
}

function smartpay_get_payment($payment_id)
{
    $payment = PaymentModel::where('id', $payment_id)->first();
    return $payment;
}

function smartpay_set_payment_transaction_id($payment_id, $transaction_id)
{
    $payment = PaymentModel::where('id', $payment_id)->first();
    if (!$payment) {
        return;
    }
    $payment->transaction_id = $transaction_id;
    $payment->save();
}
function smartpay_debug_log($message = '', $force = false)
{

    $smartpay_logs = new Logger();
    if (function_exists('mb_convert_encoding')) {
        $message = mb_convert_encoding($message, 'UTF-8');
    }

    $smartpay_logs->log_to_file($message);
}

/*
 * @return available gateways including third party integrations
 */
function smartpay_get_available_payment_gateways($availableGateways) {
    return apply_filters('smartpay_get_available_payment_gateways', $availableGateways);
}

// get the form or product title from payment id
function smartpay_get_payment_product_or_form_name($payment_id): array {
	$payment = PaymentModel::find($payment_id);
	if ($payment->type == 'Product Purchase') {
		$product = \SmartPay\Models\Product::find($payment->data['product_id']);
		if ($product) {
			$name = $product->title;
			$prev_link = $product->extra['product_preview_page_permalink'];
		}
	} else {
		$form = \SmartPay\Models\Form::find($payment->data['form_id']);
		if ($form) {
			$name = $form->title;
			$prev_link = $form->extra['form_preview_page_permalink'];
		}
	}

	return [
		'name' => $name ?? 'No Name',
		'preview'   =>$prev_link ?? '#'
	];
}

/*
 * @return updated payment data
 */
function smartpay_get_additional_payment_data($paymentData) {
    return apply_filters('smartpay_get_additional_payment_data', $paymentData);
}

function smartpay_get_customer_by_user_id($userId) {
	$customer = Customer::where('user_id', $userId)->first();

	if (empty($customer)) {
		return false;
	}

	return $customer;
}

if (!function_exists('smartpay_is_customer')) {
	/**
	 * Whether the current user may access the SmartPay account dashboard.
	 *
	 * Gated by the `access_smartpay_dashboard` capability granted to the
	 * `smartpay_customer` role (see Modules\Role\Roles).
	 */
	function smartpay_is_customer(): bool {
		return current_user_can('access_smartpay_dashboard');
	}
}

/**
 * Calculate goal progress for a form.
 *
 * @param int $form_id Form post ID.
 * @return array{current: float, target: float, percentage: float, type: string, goal_reached: bool}
 */
function smartpay_calculate_goal_progress( int $form_id ): array {
	$settings = get_post_meta( $form_id, '_smartpay_settings', true );
	$settings = is_string( $settings ) ? json_decode( $settings, true ) : ( $settings ?: [] );
	$goal     = $settings['goal'] ?? [];

	if ( empty( $goal['enabled'] ) ) {
		return [
			'current'     => 0,
			'target'      => 0,
			'percentage'  => 0,
			'type'        => 'quantity',
			'goal_reached' => false,
		];
	}

	$type   = $goal['type'] ?? 'quantity';
	$target = floatval( $goal['target'] ?? 100 );

	$transient_key = "smartpay_goal_{$form_id}_{$type}";
	$cached        = get_transient( $transient_key );

	if ( false !== $cached ) {
		$current = floatval( $cached );
	} else {
		global $wpdb;
		$table = esc_sql( $wpdb->prefix . 'smartpay_payments' );

		// Filter by form_id stored in payment data JSON
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT COUNT(*) as cnt, COALESCE(SUM(amount),0) as total_amount FROM {$table} WHERE status = %s AND data LIKE %s",
				\SmartPay\Models\Payment::COMPLETED,
				'%\"form_id\":' . (int) $form_id . '%'
			),
			ARRAY_A
		);
		// phpcs:enable

		$current = (float) ( $type === 'quantity' ? ( $row['cnt'] ?? 0 ) : ( $row['total_amount'] ?? 0 ) );

		set_transient( $transient_key, $current, MINUTE_IN_SECONDS );
	}

	$percentage = $target > 0 ? min( 100, ( $current / $target ) * 100 ) : 0;

	return [
		'current'      => $current,
		'target'       => $target,
		'percentage'   => round( $percentage, 1 ),
		'type'         => $type,
		'goal_reached' => $current >= $target,
	];
}

/**
 * Invalidate goal progress cache for a form.
 *
 * @param int $form_id Form post ID.
 */
function smartpay_invalidate_goal_cache( int $form_id ): void {
	delete_transient( "smartpay_goal_{$form_id}_quantity" );
	delete_transient( "smartpay_goal_{$form_id}_amount" );
}

/**
 * Recalculate and cache goal progress for a form.
 *
 * @param int $form_id Form post ID.
 * @param array $payment_data Payment data array containing form_id.
 */
function smartpay_recalculate_goal_cache_on_payment( int $form_id ): void {
	$settings = get_post_meta( $form_id, '_smartpay_settings', true );
	$settings = is_string( $settings ) ? json_decode( $settings, true ) : ( $settings ?: [] );
	$goal     = $settings['goal'] ?? [];

	if ( empty( $goal['enabled'] ) ) {
		return;
	}

	smartpay_invalidate_goal_cache( $form_id );
	// Trigger recalculation by deleting transient — next read recalculates.
}

/**
 * Get/set the form ID currently being rendered by the [sp_form] embed.
 *
 * Inside the shortcode, do_blocks() runs while the global post is the HOST
 * page, so get_the_ID() does not return the form CPT ID. The embed template
 * sets this context around do_blocks() so dynamic blocks (e.g. Goal Progress)
 * can resolve their owning form. Pass an int to set; call with no argument to
 * read. Returns 0 when not set.
 *
 * @param int|null $form_id Form post ID to set, or null to read.
 * @return int
 */
function smartpay_current_form_render_id( ?int $form_id = null ): int {
	static $current = 0;
	if ( null !== $form_id ) {
		$current = $form_id;
	}
	return $current;
}

/**
 * Render the Goal Progress block (smartpay-form/goal-progress) on the frontend.
 *
 * Dynamic: the live counts come from smartpay_calculate_goal_progress(), so the
 * block's save() is empty and this filter builds the markup from the block's
 * style attributes. Returns '' when the goal is disabled or no form context is
 * available. Hooked on `render_block_smartpay-form/goal-progress`.
 *
 * @param string $block_content Existing rendered content (empty for this block).
 * @param array  $block         Parsed block (name + attrs).
 * @return string
 */
function smartpay_render_goal_progress_block( string $block_content, array $block ): string {
	$form_id = smartpay_current_form_render_id();
	if ( ! $form_id ) {
		$form_id = (int) get_the_ID();
	}
	if ( ! $form_id || ! function_exists( 'smartpay_calculate_goal_progress' ) ) {
		return '';
	}

	$settings = get_post_meta( $form_id, '_smartpay_settings', true );
	$settings = is_string( $settings ) ? json_decode( $settings, true ) : ( $settings ?: array() );
	$raw_goal = $settings['goal'] ?? array();
	$goal     = is_string( $raw_goal ) ? json_decode( $raw_goal, true ) : $raw_goal;
	$goal     = is_array( $goal ) ? $goal : array();

	if ( empty( $goal['enabled'] ) ) {
		return '';
	}

	$progress    = smartpay_calculate_goal_progress( $form_id );
	$current     = (float) $progress['current'];
	$target      = (float) $progress['target'];
	$percentage  = (float) $progress['percentage'];
	$reached     = ! empty( $progress['goal_reached'] );
	$type        = $goal['type'] ?? 'quantity';
	$unit        = 'quantity' === $type ? _n( 'sold', 'sold', (int) floor( $current ), 'smartpay' ) : __( 'raised', 'smartpay' );
	$met_message = $goal['goalMetMessage'] ?? __( 'Goal reached!', 'smartpay' );

	$a = is_array( $block['attrs'] ?? null ) ? $block['attrs'] : array();

	$show_bar   = ! isset( $a['showBar'] ) || $a['showBar'];
	$show_count = ! isset( $a['showCounts'] ) || $a['showCounts'];
	$show_pct   = ! isset( $a['showPercentage'] ) || $a['showPercentage'];
	$show_msg   = ! isset( $a['showMessage'] ) || $a['showMessage'];
	$tpl        = isset( $a['messageTemplate'] ) ? (string) $a['messageTemplate'] : '';

	$bg     = sanitize_text_field( $a['bgColor'] ?? '#f8f9fa' );
	$bar    = sanitize_text_field( $a['barColor'] ?? '#28a745' );
	$track  = sanitize_text_field( $a['trackColor'] ?? '#e9ecef' );
	$text   = sanitize_text_field( $a['textColor'] ?? '#555555' );
	$bar_h  = absint( $a['barHeight'] ?? 12 );
	$bar_r  = absint( $a['barRadius'] ?? 4 );
	$card_r = absint( $a['cardRadius'] ?? 8 );
	$pad    = absint( $a['padding'] ?? 16 );
	$fs     = absint( $a['fontSize'] ?? 14 );

	$card_style = sprintf(
		'margin-bottom:20px;padding:%dpx;background:%s;border-radius:%dpx;text-align:left;color:%s;font-size:%dpx;',
		$pad,
		$bg,
		$card_r,
		$text,
		$fs
	);

	ob_start();
	?>
	<div class="smartpay-goal-progress" style="<?php echo esc_attr( $card_style ); ?>">
		<?php if ( $reached && $show_msg ) : ?>
			<p style="margin:0 0 12px;font-weight:600;color:<?php echo esc_attr( $bar ); ?>;">
				<?php echo esc_html( $met_message ); ?>
			</p>
		<?php elseif ( $show_count ) : ?>
			<?php if ( '' !== $tpl ) : ?>
				<p style="margin:0 0 8px;">
					<?php
					echo esc_html(
						strtr(
							$tpl,
							array(
								'{current}' => number_format( $current ),
								'{target}'  => number_format( $target ),
								'{percent}' => (string) $percentage,
								'{unit}'    => $unit,
							)
						)
					);
					?>
				</p>
			<?php else : ?>
				<p style="margin:0 0 8px;">
					<strong><?php echo esc_html( number_format( $current ) ); ?></strong>
					/ <?php echo esc_html( number_format( $target ) ); ?> <?php echo esc_html( $unit ); ?>
				</p>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( $show_bar ) : ?>
			<div style="background:<?php echo esc_attr( $track ); ?>;border-radius:<?php echo esc_attr( $bar_r ); ?>px;height:<?php echo esc_attr( $bar_h ); ?>px;overflow:hidden;">
				<div style="width:<?php echo esc_attr( $percentage ); ?>%;background:<?php echo esc_attr( $bar ); ?>;height:100%;border-radius:<?php echo esc_attr( $bar_r ); ?>px;transition:width 0.3s ease;"></div>
			</div>
		<?php endif; ?>

		<?php if ( $show_pct && ! $reached ) : ?>
			<p style="margin:8px 0 0;font-size:0.85em;text-align:right;opacity:0.7;"><?php echo esc_html( $percentage ); ?>%</p>
		<?php endif; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Read the Submit Button block's attributes from a form's content.
 *
 * The Submit Button block (smartpay-form/submit-button) renders nothing inline;
 * its attributes drive the button + related options (e.g. coupon visibility).
 * Returns the attributes array, or null when the form has no submit button block.
 *
 * @param int $form_id Form post ID.
 * @return array|null
 */
function smartpay_get_submit_button_attrs( int $form_id ): ?array {
	if ( $form_id <= 0 || ! has_block( 'smartpay-form/submit-button', $form_id ) ) {
		return null;
	}

	$post = get_post( $form_id );
	if ( ! $post instanceof \WP_Post ) {
		return null;
	}

	foreach ( parse_blocks( $post->post_content ) as $block ) {
		if ( 'smartpay-form/submit-button' === ( $block['blockName'] ?? '' ) ) {
			return is_array( $block['attrs'] ?? null ) ? $block['attrs'] : array();
		}
	}

	return null;
}

/**
 * Read a Submit Button child block's attributes (Coupon or Pay Button).
 *
 * The Submit Button block (smartpay-form/submit-button) is an InnerBlocks
 * container holding a Coupon child (smartpay-form/submit-coupon) and a Pay
 * Button child (smartpay-form/submit-pay). Returns the named child's attributes,
 * or null when the parent or that child is absent (e.g. the coupon child was
 * removed to hide the coupon section).
 *
 * @param int    $form_id    Form post ID.
 * @param string $child_name Child block name.
 * @return array|null
 */
function smartpay_get_submit_child_attrs( int $form_id, string $child_name ): ?array {
	if ( $form_id <= 0 || ! has_block( 'smartpay-form/submit-button', $form_id ) ) {
		return null;
	}

	$post = get_post( $form_id );
	if ( ! $post instanceof \WP_Post ) {
		return null;
	}

	// Find the submit-button block anywhere in the tree (it may be nested inside
	// a Group/Columns), then return the requested child's attributes.
	$submit_button = smartpay_find_block_recursive( parse_blocks( $post->post_content ), 'smartpay-form/submit-button' );
	if ( null === $submit_button ) {
		return null;
	}

	foreach ( (array) ( $submit_button['innerBlocks'] ?? array() ) as $child ) {
		if ( $child_name === ( $child['blockName'] ?? '' ) ) {
			return is_array( $child['attrs'] ?? null ) ? $child['attrs'] : array();
		}
	}

	return null;
}

/**
 * Depth-first search a parsed-block tree for the first block of a given name.
 *
 * @param array  $blocks Parsed blocks (from parse_blocks()).
 * @param string $name   Block name to find.
 * @return array|null The matching block array, or null when not found.
 */
function smartpay_find_block_recursive( array $blocks, string $name ): ?array {
	foreach ( $blocks as $block ) {
		$block_name = $block['blockName'] ?? '';
		if ( $name === $block_name ) {
			return $block;
		}
		if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
			$found = smartpay_find_block_recursive( $block['innerBlocks'], $name );
			if ( null !== $found ) {
				return $found;
			}
		}
	}

	return null;
}

/**
 * Inline SVG markup for a Pay Button icon slug.
 *
 * Slugs mirror resources/form-builder/blocks/SubmitButton/icons.js so the
 * editor preview and the rendered frontend button use the same icon set.
 * Returns an empty string for an unknown or empty slug. Inherits the button
 * text colour via `currentColor`.
 *
 * @param string $slug Icon slug (e.g. 'arrow-right', 'lock', 'cart').
 * @return string SVG markup, or '' when there is no matching icon.
 */
function smartpay_submit_button_icon_svg( string $slug ): string {
	$paths = [
		'arrow-right' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'lock'        => '<rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
		'cart'        => '<circle cx="9" cy="20" r="1"/><circle cx="17" cy="20" r="1"/><path d="M3 4h2l2.4 12.4a1 1 0 0 0 1 .6h8.2a1 1 0 0 0 1-.8L21 8H6"/>',
		'check'       => '<path d="M5 12l5 5L20 6"/>',
		'dollar'      => '<path d="M12 2v20M17 6H10a3 3 0 0 0 0 6h4a3 3 0 0 1 0 6H7"/>',
	];

	if ( empty( $slug ) || ! isset( $paths[ $slug ] ) ) {
		return '';
	}

	return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths[ $slug ] . '</svg>';
}

/**
 * Record a payment activity log entry.
 *
 * @param int    $payment_id Payment ID.
 * @param string $action     Action key (e.g. 'status_changed', 'admin_note').
 * @param string $note       Optional human-readable note.
 * @return \SmartPay\Models\PaymentLog|null
 */
function smartpay_record_payment_log( int $payment_id, string $action, string $note = '' ) {
	if ( $payment_id <= 0 ) {
		return null;
	}

	$log             = new \SmartPay\Models\PaymentLog();
	$log->payment_id = $payment_id;
	$log->user_id    = get_current_user_id() ?: null;
	$log->action     = sanitize_key( $action );
	$log->note       = sanitize_textarea_field( $note );
	$log->created_at = current_time( 'mysql', true );
	$log->save();

	return $log;
}

if ( ! function_exists( 'smartpay_is_pro_active' ) ) {
	/**
	 * Whether SmartPay Pro is active and licensed.
	 *
	 * Defaults to false. The Pro plugin hooks `smartpay_is_pro_active` to return
	 * true once installed, active, and licensed (valid or in grace period).
	 *
	 * @return bool
	 */
	function smartpay_is_pro_active(): bool {
		return (bool) apply_filters( 'smartpay_is_pro_active', false );
	}
}

if ( ! function_exists( 'smartpay_pro_feature_available' ) ) {
	/**
	 * Whether a specific Pro feature is available.
	 *
	 * @param string $feature Feature slug (e.g. 'subscription').
	 * @return bool
	 */
	function smartpay_pro_feature_available( string $feature ): bool {
		return (bool) apply_filters( 'smartpay_pro_feature_available', false, $feature );
	}
}

